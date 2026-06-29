<!-- Modal Proveedor -->
<div class="modal fade" id="modal_nuevo_proveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); border-bottom: none;">
                <h5 class="modal-title fw-bold" id="modalProveedorLabel">
                    <i class="fas fa-truck me-2"></i>Registrar Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_nuevo_proveedor" autocomplete="off">
                @csrf
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="row g-3">
                        <!-- Tipo de proveedor -->
                        <div class="col-md-6">
                            <label for="mp_tipo_persona" class="form-label small fw-bold text-muted">Tipo de proveedor:</label>
                            <select class="form-select form-select-sm" name="tipo_persona" id="mp_tipo_persona" required style="border-radius: 6px;">
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option value="natural">Persona natural</option>
                                <option value="juridica">Persona jurídica</option>
                            </select>
                        </div>

                        <!-- Razón social / Nombre -->
                        <div class="col-md-6" id="mp_box_razon_social" style="display: none;">
                            <label id="mp_label_natural" for="mp_razon_social" class="form-label small fw-bold text-muted">Nombres y apellidos:</label>
                            <label id="mp_label_juridica" for="mp_razon_social" class="form-label small fw-bold text-muted">Nombre de la empresa:</label>
                            <input required type="text" name="razon_social" id="mp_razon_social" class="form-control form-control-sm" placeholder="..." style="border-radius: 6px;">
                        </div>

                        <!-- Dirección -->
                        <div class="col-12">
                            <label for="mp_direccion" class="form-label small fw-bold text-muted">Dirección:</label>
                            <input required type="text" name="direccion" id="mp_direccion" class="form-control form-control-sm" placeholder="Ingrese la dirección completa" style="border-radius: 6px;">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12">
                            <label for="mp_telefono" class="form-label small fw-bold text-muted">Teléfono:</label>
                            <input required type="text" name="telefono" id="mp_telefono" class="form-control form-control-sm" placeholder="Ingrese el número de teléfono" style="border-radius: 6px;">
                        </div>

                        <!-- Tipo de documento -->
                        <div class="col-md-6">
                            <label for="mp_documento_id" class="form-label small fw-bold text-muted">Tipo de documento:</label>
                            <select class="form-select form-select-sm" name="documento_id" id="mp_documento_id" required style="border-radius: 6px;">
                                <option value="" selected disabled>Cargando documentos...</option>
                            </select>
                        </div>

                        <!-- Número de documento -->
                        <div class="col-md-6">
                            <label for="mp_numero_documento" class="form-label small fw-bold text-muted">Número de documento:</label>
                            <input required type="text" name="numero_documento" id="mp_numero_documento" class="form-control form-control-sm" placeholder="Ingrese el número de documento" style="border-radius: 6px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3" style="background-color: #f1f3f5;">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal" style="border-radius: 6px;">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); border: none; border-radius: 6px; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);">
                        <i class="fas fa-save me-2"></i>Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let metadataLoaded = false;

        // Ocultar labels dinámicos inicialmente
        $('#mp_label_natural').hide();
        $('#mp_label_juridica').hide();

        $('#mp_tipo_persona').on('change', function() {
            let selectValue = $(this).val();
            if (selectValue === 'natural') {
                $('#mp_label_juridica').hide();
                $('#mp_label_natural').show();
                $('#mp_razon_social').attr('placeholder', 'Ej: Juan Pérez López');
            } else if (selectValue === 'juridica') {
                $('#mp_label_natural').hide();
                $('#mp_label_juridica').show();
                $('#mp_razon_social').attr('placeholder', 'Ej: Distribuidora XYZ S.A.');
            }
            $('#mp_box_razon_social').slideDown(200);
        });

        // Cargar metadata al abrir el modal
        $('#modal_nuevo_proveedor').on('show.bs.modal', function () {
            if (!metadataLoaded) {
                $.ajax({
                    url: '{{ route("metadata.documentos-grupos") }}',
                    method: 'GET',
                    success: function(data) {
                        // Cargar Documentos
                        let docSelect = $('#mp_documento_id');
                        docSelect.empty().append('<option value="" selected disabled>Seleccione documento</option>');
                        data.documentos.forEach(function(item) {
                            docSelect.append(`<option value="${item.id}">${item.tipo_documento}</option>`);
                        });

                        metadataLoaded = true;
                    },
                    error: function() {
                        console.error('Error al cargar la metadata para el modal de proveedores.');
                    }
                });
            }
        });

        // Enviar formulario mediante AJAX
        $('#form_nuevo_proveedor').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Registrando proveedor...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '{{ route("proveedores.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Resetear formulario
                        $('#form_nuevo_proveedor')[0].reset();
                        $('#mp_box_razon_social').hide();
                        $('#modal_nuevo_proveedor').modal('hide');

                        // Si existe la función callback global en la página, la ejecutamos
                        if (typeof window.onProveedorCreado === 'function') {
                            window.onProveedorCreado(response.proveedor);
                        }
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo guardar el proveedor.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    let errMsg = 'Ocurrió un error al registrar el proveedor.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Validación/Error', errMsg, 'warning');
                }
            });
        });
    });
</script>
