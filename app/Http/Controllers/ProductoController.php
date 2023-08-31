<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Producto; 
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::with('marca','categoria')->get();//Metodo para que al traernos todos los productos los traiga tambien con la informacion de la marca y la categoria del productos.
            return ApiResponse::success('Lista de productos',200,$productos);
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de productos: '.$e->getMessage(),500);
        }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
               'nombre' => 'required|unique:productos',
               'precio' => 'required|numeric|between:0,999999.99',
               'cantidad_disponible' => 'required|integer',
               'categoria_id' => 'required|exists:categorias,id',
               'marca_id' => 'required|exists:marcas,id'
            ]);

            $producto = Producto::create($request->all());

            return ApiResponse::success('Producto creado exitosamente',201,$producto);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();//sirve para convertir todos los errores en un array para que nos muestre todos los errores que tengamos.

            if (isset($errors['categoria_id'])) {//esto sirve para que nos muestre categoria enves de categoria_id.
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {//esto sirve para que nos muestre marca enves de marca_id.
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }

            return ApiResponse::error('Errores de validación: ',422,$errors);
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::with('marca','categoria')->findOrFail($id);
            return ApiResponse::success('Producto obtenido exitosamente',200,$producto);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado',404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $request->validate([
               'nombre' => 'required|unique:productos,nombre,'.$producto->id,
               'precio' => 'required|numeric|between:0,999999.99',
               'cantidad_disponible' => 'required|integer',
               'categoria_id' => 'required|exists:categorias,id',
               'marca_id' => 'required|exists:marcas,id'
            ]);
            $producto->update($request->all());
            return ApiResponse::success('Producto actualizado exitosamente',200,$producto);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();//sirve para convertir todos los errores en un array para que nos muestre todos los errores que tengamos.

            if (isset($errors['categoria_id'])) {//esto sirve para que nos muestre categoria enves de categoria_id.
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {//esto sirve para que nos muestre marca enves de marca_id.
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }

            return ApiResponse::error('Errores de validación: ',422,$errors);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado',404);
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return ApiResponse::success('Producto eliminado exitosamente',200);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no encontrado',404);
        }
    }
}
