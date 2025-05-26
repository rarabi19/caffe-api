<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\TransaksiController;
use App\Http\Controllers\API\UkuranController;
use App\Http\Controllers\API\OrderanController;
use App\Http\Controllers\API\ToppingController;
use App\Http\Controllers\API\KategoriController;;

        // AUTHENTICATION
        Route::prefix('auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'getProfile']);
            Route::put('/me/update', [AuthController::class, 'updateProfile']);
            Route::put('/me/password', [AuthController::class, 'changePassword']);
        });

         // KATEGORI
        Route::prefix('kategori')->group(function () {
            Route::get('/', [KategoriController::class, 'index']);
            Route::get('/v2', [KategoriController::class, 'index2']);
            Route::get('/{id}', [KategoriController::class, 'show']);
        });

        // MENU MINUMAN
        Route::prefix('menu')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::get('/v2', [MenuController::class, 'index2']);
            Route::get('/list', [MenuController::class, 'simpleList']);
            Route::get('/{id}', [MenuController::class, 'show']);
            Route::get('/kategori/{id_kategori}', [MenuController::class, 'byKategori']);
            });

        // TOPPING
        Route::prefix('topping')->group(function () {
            Route::get('/', [ToppingController::class, 'index']);
            Route::get('/v2', [ToppingController::class, 'index2']);
            Route::get('/{id}', [ToppingController::class, 'show']);
            });

        // UKURAN
        Route::prefix('ukuran')->group(function () {
            Route::get('/', [UkuranController::class, 'index']);
            Route::get('/v2', [UkuranController::class, 'index2']);
            Route::get('/v3', [UkuranController::class, 'index3']);
            Route::get('/{id}', [UkuranController::class, 'show']);
            });
            
        // ORDERAN / KERANJANG
        Route::prefix('orderan')->group(function () {
            Route::post('/', [OrderanController::class, 'buatOrder']); 
            Route::get('/{id_orderan}', [OrderanController::class, 'ListOrderan']); 
            Route::put('/{id_orderan}', [OrderanController::class, 'updateOrderan']);
            Route::delete('/{id_orderan}', [OrderanController::class, 'hapusOrderan']); 
        });

        // TRANSAKSI
        Route::prefix('transaksi')->group(function () {
            Route::post('/bayar', [TransaksiController::class, 'checkout']);   
            Route::get('/', [TransaksiController::class, 'index']);             
            Route::get('/struk/{id}', [TransaksiController::class, 'showStruk']); 
            Route::put('/{id}', [TransaksiController::class, 'update']);       
            Route::delete('/{id}', [TransaksiController::class, 'destroy']);     
        });

        // Route::prefix('user')->group(function () {
        //     Route::get('/dasbor',       [AuthController::class, 'getAllUsers']);
        //     Route::get('/dasbor/{id}', [AuthController::class, 'getUser']);
        //     Route::put('/dasbor/{id}', [AuthController::class, 'updateUser']);
        //     Route::delete('/dasbor/{id}', [AuthController::class, 'deleteUser']);
        // });
