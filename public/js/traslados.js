// Productos cargados desde el backend (vienen en window.productosData)
let productosData = [];

// Estado de la aplicación
let productoSeleccionado = null;
let detalle = [];
let contador = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos de productos del window
    if (window.productosData) {
        productosData = window.productosData;
    }
    
    // Cargar detalles existentes si es página de edit
    if (window.isEdit && window.detallesExistentes) {
        let contador = 0;
        detalle = window.detallesExistentes.map(d => {
            contador++;
            return {
                contador: contador,
                id: d.producto_id,
                nombre: d.producto.nombre,
                codigo: d.producto.codigo,
                cantidad: d.cantidad,
                stock: d.producto.inventarios.reduce((sum, inv) => sum + inv.stock, 0)
            };
        });
    }
    
    initializeEventListeners();
    renderDetalle();
});

// Inicializar event listeners
function initializeEventListeners() {
    document.getElementById('producto_search')?.addEventListener('keyup', handleProductoSearch);
    document.getElementById('origen_almacen_id')?.addEventListener('change', handleAlmacenChange);
    document.getElementById('destino_almacen_id')?.addEventListener('change', handleAlmacenChange);
    document.getElementById('btn_agregar')?.addEventListener('click', agregarProducto);
    document.getElementById('trasladoForm')?.addEventListener('submit', validarFormulario);
}

// Buscar y mostrar productos
function handleProductoSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const dropdown = document.getElementById('products_dropdown');
    if (searchTerm.length === 0) return dropdown.classList.remove('show');

    const filtrados = productosData.filter(p =>
        p.nombre.toLowerCase().includes(searchTerm) || p.codigo.toLowerCase().includes(searchTerm)
    );

    if (filtrados.length === 0) {
        dropdown.innerHTML = '<div class="text-muted p-2">No se encontraron productos</div>';
        dropdown.classList.add('show');
        return;
    }

    dropdown.innerHTML = filtrados.map(p => {
        const inventarios = p.inventarios || [];
        const totalStock = inventarios.reduce((sum, inv) => sum + (inv.stock || 0), 0);
        return `
            <div class="product-item" data-id="${p.id}" data-nombre="${p.nombre}" 
                 data-codigo="${p.codigo}" data-inventarios='${JSON.stringify(inventarios)}'>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>${p.nombre}</strong><br>
                        <small class="text-muted">${p.codigo}</small>
                    </div>
                    <span class="badge bg-info" style="white-space: nowrap;">${Math.floor(totalStock)}</span>
                </div>
            </div>
        `;
    }).join('');

    dropdown.classList.add('show');

    document.querySelectorAll('.product-item').forEach(item => item.addEventListener('click', seleccionarProducto));
}

// Seleccionar un producto
function seleccionarProducto(e) {
    const item = e.currentTarget;
    const inventarios = JSON.parse(item.dataset.inventarios);
    const origenAlmacenId = parseInt(document.getElementById('origen_almacen_id').value);
    let stockEnOrigen = 0;
    if (origenAlmacenId) {
        const inv = inventarios.find(i => i.almacen_id === origenAlmacenId);
        stockEnOrigen = inv ? inv.stock : 0;
    }

    productoSeleccionado = {
        id: parseInt(item.dataset.id),
        nombre: item.dataset.nombre,
        codigo: item.dataset.codigo,
        inventarios: inventarios,
        stock: stockEnOrigen
    };

    document.getElementById('producto_search').value = productoSeleccionado.nombre;
    document.getElementById('stock').value = Math.floor(productoSeleccionado.stock);
    document.getElementById('products_dropdown').classList.remove('show');
}

// Cambio de almacenes
function handleAlmacenChange() {
    const origenId = parseInt(document.getElementById('origen_almacen_id').value);
    const destinoId = parseInt(document.getElementById('destino_almacen_id').value);

    // Limpiar todo cuando cambia un almacén
    limpiarTodoProductos();

    // Validar que no sean iguales
    if (origenId && destinoId && origenId === destinoId) {
        Swal.fire('Error', 'El almacén origen y destino no pueden ser iguales', 'error');
        document.getElementById('destino_almacen_id').value = '';
        document.getElementById('productos_section').style.display = 'none';
        return;
    }

    // Mostrar/ocultar sección de productos solo si ambos almacenes están seleccionados
    const productosSection = document.getElementById('productos_section');
    if (origenId && destinoId && origenId !== destinoId) {
        productosSection.style.display = 'block';
    } else {
        productosSection.style.display = 'none';
    }

    // Deshabilitar opciones que ya estén seleccionadas
    document.querySelectorAll('#origen_almacen_id option').forEach(option => {
        option.disabled = parseInt(option.value) === destinoId && option.value !== '';
    });
    document.querySelectorAll('#destino_almacen_id option').forEach(option => {
        option.disabled = parseInt(option.value) === origenId && option.value !== '';
    });
}

// Agregar producto al detalle
function agregarProducto() {
    if (!productoSeleccionado) return Swal.fire('Advertencia', 'Seleccione un producto', 'warning');

    const origenId = parseInt(document.getElementById('origen_almacen_id').value);
    if (!origenId) return Swal.fire('Advertencia', 'Debe seleccionar un almacén origen', 'warning');

    const cantidad = parseInt(document.getElementById('cantidad').value);
    if (!cantidad || cantidad <= 0) return Swal.fire('Advertencia', 'La cantidad debe ser mayor a 0', 'warning');
    
    const stockDisponible = Math.floor(productoSeleccionado.stock) || 0;
    if (cantidad > stockDisponible) return Swal.fire('Advertencia', `Cantidad supera el stock disponible. Stock: ${stockDisponible}`, 'warning');

    const existe = detalle.find(d => d.id === productoSeleccionado.id);
    const cantidadTotal = existe ? existe.cantidad + cantidad : cantidad;
    
    if (cantidadTotal > stockDisponible) {
    return Swal.fire({
        icon: 'warning',
        title: 'Advertencia',
        html: `
            <div style="text-align:center">
                <p>Ya agregaste: <strong>${(existe?.cantidad || 0)}</strong></p>
                <p>Total solicitado: <strong>${cantidadTotal}</strong></p>
                <p>Stock disponible: <strong>${stockDisponible}</strong></p>
            </div>
        `
    });
}


    
    if (existe) {
        existe.cantidad += cantidad;
    } else {
        contador++;
        detalle.push({
            contador,
            id: productoSeleccionado.id,
            nombre: productoSeleccionado.nombre,
            codigo: productoSeleccionado.codigo,
            cantidad,
            stock: productoSeleccionado.stock
        });
    }

    renderDetalle();
    limpiarCampos();
}

// Renderizar detalle
function renderDetalle() {
    const tbody = document.getElementById('tbody_detalle');
    const sinProductos = document.getElementById('sin_productos');

    tbody.innerHTML = detalle.map(item => `
        <tr>
            <td>${item.contador}</td>
            <td>${item.nombre} <br> <small class="text-muted">${item.codigo}</small></td>
            <td>
                <input type="hidden" name="arrayidproducto[]" value="${item.id}">
                <input type="hidden" name="arraycantidad[]" value="${item.cantidad}">
                ${Math.floor(item.cantidad)}
            </td>
            <td>${Math.floor(item.stock)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    sinProductos.style.display = detalle.length === 0 ? 'block' : 'none';
}

// Eliminar producto del detalle
function eliminarProducto(id) {
    detalle = detalle.filter(d => d.id !== id);
    renderDetalle();
}

// Limpiar campos
function limpiarCampos() {
    document.getElementById('producto_search').value = '';
    document.getElementById('cantidad').value = '1';
    document.getElementById('stock').value = '';
    productoSeleccionado = null;
}

// Limpiar todo (productos agregados + campos)
function limpiarTodoProductos() {
    detalle = [];
    contador = 0;
    limpiarCampos();
    renderDetalle();
}

// Validar formulario antes de enviar
function validarFormulario(e) {
    if (detalle.length === 0) {
        e.preventDefault();
        return Swal.fire('Advertencia', 'Debe agregar al menos un producto', 'warning');
    }

    const origenId = document.getElementById('origen_almacen_id').value;
    const destinoId = document.getElementById('destino_almacen_id').value;

    if (!origenId) {
        e.preventDefault();
        return Swal.fire('Advertencia', 'Debe seleccionar un almacén origen', 'warning');
    }

    if (!destinoId) {
        e.preventDefault();
        return Swal.fire('Advertencia', 'Debe seleccionar un almacén destino', 'warning');
    }

    if (origenId === destinoId) {
        e.preventDefault();
        return Swal.fire('Error', 'El almacén origen y destino no pueden ser iguales', 'error');
    }
}
