<?php

use App\Http\Controllers\Admin\AlmacenController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\CompraController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\GrupoClientesController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\MarcaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProveedorController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TipoUnidadController;
use App\Http\Controllers\Admin\TrasladoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\CotizacionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');
Route::prefix('admin')->group(function () {
    // --- Autenticacion ---
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login');
    });
    // --- Facturas públicas (links firmados para compartir por WhatsApp) ---
    Route::middleware('signed')->group(function () {
        Route::get('/facturas/ventas/{venta}', [VentaController::class, 'facturaPublica'])->name('facturas.ventas');
        Route::get('/facturas/compras/{compra}', [CompraController::class, 'facturaPublica'])->name('facturas.compras');
    });

    Route::middleware('auth')->group(function () {
        // --- Exportacion Universal ---
        Route::prefix('export')->name('export.')->group(function () {
            Route::post('/{module}/excel', [ExportController::class, 'exportExcel'])->name('excel');
            Route::post('/{module}/pdf', [ExportController::class, 'exportPdf'])->name('pdf');
        });
        // --- Dashboard ---
        Route::get('/', [HomeController::class, 'index'])->name('panel');
        // --- Perfil de Usuario ---
        Route::resource('profile', ProfileController::class);
        // --- Gestion de Productos ---
        Route::prefix('productos')->name('productos.')->group(function () {
            // Ajustes de Stock
            Route::get('/historial-ajustes', [ProductoController::class, 'historialAjustes'])->name('historialAjustes');
            Route::get('/crear-ajuste', [ProductoController::class, 'createAjuste'])->name('createAjuste');
            Route::post('/store-ajuste', [ProductoController::class, 'storeAjuste'])->name('storeAjuste');
            Route::get('/{producto}/ajuste-cantidad', [ProductoController::class, 'ajusteCantidad'])->name('ajusteCantidad');
            Route::post('/{producto}/ajuste-cantidad', [ProductoController::class, 'updateCantidad'])->name('updateCantidad');
            // Utilidades
            Route::get('/check-stock', [ProductoController::class, 'checkStock'])->name('checkStock');
            Route::patch('/{producto}/estado', [ProductoController::class, 'updateEstado'])->name('updateEstado');
            // Exportacion
            Route::post('/export-excel', [ProductoController::class, 'exportExcel'])->name('export.excel');
            Route::post('/export-pdf', [ProductoController::class, 'exportPdf'])->name('export.pdf');
        });
        // --- Gestion de Ventas ---
        Route::prefix('ventas')->name('ventas.')->group(function () {
            Route::get('/check-stock', [VentaController::class, 'checkStock'])->name('check-stock');
            Route::put('/{venta}/estado-pago', [VentaController::class, 'actualizarEstadoPago'])->name('estado-pago');
            Route::put('/{venta}/estado-entrega', [VentaController::class, 'actualizarEstadoEntrega'])->name('estado-entrega');
            Route::get('/pdf/{id}', [VentaController::class, 'generarPdf'])->name('pdf');
            Route::get('/reporte-caja', [VentaController::class, 'reporteCaja'])->name('reporte-caja');
            Route::get('/reporte-caja/export/excel', [VentaController::class, 'exportReporteCajaExcel'])->name('reporte-caja.excel');
            Route::get('/reporte-caja/export/pdf', [VentaController::class, 'exportReporteCajaPdf'])->name('reporte-caja.pdf');
        });

        // --- Gestion de Compras ---
        Route::prefix('compras')->name('compras.')->group(function () {
            Route::put('/{compra}/estado-pago', [CompraController::class, 'actualizarEstadoPago'])->name('estado-pago');
            Route::put('/{compra}/estado-entrega', [CompraController::class, 'actualizarEstadoEntrega'])->name('estado-entrega');
            Route::get('/pdf/{id}', [CompraController::class, 'generarPdf'])->name('pdf');
        });

        // --- Gestion de Cotizaciones ---
        Route::prefix('cotizaciones')->name('cotizaciones.')->group(function () {
            Route::post('/{cotizacion}/convertir-venta', [CotizacionController::class, 'convertirVenta'])->name('convertir-venta');
            Route::post('/{cotizacion}/convertir-compra', [CotizacionController::class, 'convertirCompra'])->name('convertir-compra');
            Route::get('/pdf/{cotizacion}', [CotizacionController::class, 'generarPdf'])->name('pdf');
        });

        // --- Gestion de Traslados ---
        Route::prefix('traslados')->name('traslados.')->group(function () {
            Route::patch('/{traslado}/update-estado', [TrasladoController::class, 'toggleEstado'])->name('toggleEstado');
            Route::get('/{traslado}/detalles', [TrasladoController::class, 'getDetalles'])->name('getDetalles');
            Route::post('/exportar/excel', [TrasladoController::class, 'exportarExcel'])->name('exportar-excel');
            Route::post('/exportar/pdf', [TrasladoController::class, 'exportarPdf'])->name('exportar-pdf');
        });

        // --- Almacenes ---
        Route::patch('/almacenes/{almacen}/estado', [AlmacenController::class, 'updateEstado'])->name('almacenes.updateEstado');

        // --- Recursos del Sistema ---
        Route::resources([
            'categorias'    => CategoriaController::class,
            'marcas'        => MarcaController::class,
            'productos'     => ProductoController::class,
            'clientes'      => ClienteController::class,
            'proveedores'   => ProveedorController::class,
            'compras'       => CompraController::class,
            'ventas'        => VentaController::class,
            'users'         => UserController::class,
            'roles'         => RoleController::class,
            'traslados'     => TrasladoController::class,
            'almacenes'     => AlmacenController::class,
            'grupoclientes' => GrupoClientesController::class,
            'cotizaciones'  => CotizacionController::class,
        ], [
            'parameters' => ['almacenes' => 'almacen', 'cotizaciones' => 'cotizacion']
        ]);

        // --- Estado de cliente (activar/desactivar) ---
        Route::patch('clientes/{persona}/estado', [ClienteController::class, 'changeState'])->name('clientes.changeState');

        // --- Metadata para modales ---
        Route::get('metadata/documentos-grupos', [ClienteController::class, 'getMetadata'])->name('metadata.documentos-grupos');

        Route::resource('tipounidades', TipoUnidadController::class)->parameters(['tipounidades' => 'tipounidad']);

        Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
    });

    // --- Paginas de Error ---
    Route::get('/401', fn() => view('admin.pages.401'));
    Route::get('/404', fn() => view('admin.pages.404'));
    Route::get('/500', fn() => view('admin.pages.500'));
});
