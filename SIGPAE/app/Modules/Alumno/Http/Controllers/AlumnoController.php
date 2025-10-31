<?php

namespace App\Modules\Alumno\Http\Controllers;


use App\Http\Controllers\Controller;   // controlador base de Laravel
use App\Modules\Alumno\Models\Alumno;
use App\Modules\Alumno\Models\Persona;
use App\Modules\Alumno\Models\Aula;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    // Muestra el listado de alumnos y filtros
    public function index(Request $request) {
        $query = Alumno::with(['persona', 'aula']);

        if ($request->filled('aula')) {
            $query->where('aula_id', $request->aula);
        }

        if ($request->filled('nombre')) {
        $query->whereHas('persona', function ($q) use ($request) {
            $q->where('nombre', 'like', '%' . $request->nombre . '%');
        });
        }

        if ($request->filled('apellido')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('apellido', 'like', '%' . $request->apellido . '%');
            });
        }

        if ($request->filled('dni')) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('dni', 'like', '%' . $request->dni . '%');
            });
        }

        $alumnos = $query->paginate(20);
        $aulas = Aula::all();

        return view('Alumno::index', compact('alumnos', 'aulas'));
    }

    //Muestra el formulario de creación.
    
    public function create()
    {
        $aulas = Aula::all();
        return view('Alumno::formAlumno', [
            'alumno' => new Alumno(),
            'aulas' => $aulas,
            'modo' => 'create'
        ]);
    }

    //Guarda un nuevo alumno en la base de datos. (primero crea la persona asociada)
    public function store(Request $request) {

      $request->validate([
          'nombre' => 'required|string',
          'apellido' => 'required|string',
          'dni' => 'required|numeric|unique:personas,dni',
          'aula_id' => 'required|exists:aulas,id',
      ]);

      // 1. Crear la persona
      $persona = new Persona();
      $persona->nombre = $request->nombre;
      $persona->apellido = $request->apellido;
      $persona->dni = $request->dni;
      $persona->save();

      // 2. Crear el alumno asociado
      $alumno = new Alumno();
      $alumno->persona_id = $persona->id;
      $alumno->aula_id = $request->aula_id;
      $alumno->save();

      return redirect()->route('alumnos.index')->with('success', 'Alumno creado correctamente.');
  }

  /*
  Muestra un alumno en detalle.
    public function show($id)
    {
        $alumno = Alumno::with(['persona', 'aula'])->findOrFail($id);
        return view('Alumno::show', compact('alumno'));
    }
  */

    // Muestra el formulario de edición.
    public function edit($id) {
      $alumno = Alumno::with('persona')->findOrFail($id);
      $aulas = Aula::all();

      return view('Alumno::formAlumno', [
          'alumno' => $alumno,
          'persona' => $alumno->persona,
          'aulas' => $aulas,
          'modo' => 'edit'
      ]);
    }

    // Actualiza un alumno existente.
    public function update(Request $request, $id)
    {
      $request->validate([
        'nombre' => 'required|string',
        'apellido' => 'required|string',
        'dni' => 'required|numeric|unique:personas,dni,' . $id,
        'aula_id' => 'required|exists:aulas,id',
      ]);

      $alumno = Alumno::with('persona')->findOrFail($id);

      // Actualizar persona
      $alumno->persona->update([
          'nombre' => $request->nombre,
          'apellido' => $request->apellido,
          'dni' => $request->dni,
      ]);

      // Actualizar alumno
      $alumno->update([
          'aula_id' => $request->aula_id,
      ]);

      return redirect()->route('alumnos.index')->with('success', 'Alumno actualizado correctamente');
    }

    // Elimina un alumno.
    public function destroy($id)
    {
        $alumno = Alumno::findOrFail($id);
        $alumno->delete();

        return redirect()->route('alumnos.index')->with('success', 'Alumno eliminado correctamente');
    }
}
