@extends('layouts.base')
@section('encabezado', 'Todos los Alumnos')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold">Listado de Alumnos</h2>

        <a href="{{ route('alumnos.create') }}" class="btn btn-success">
            Registrar Alumno
        </a>
    </div>

    <!-- Filtros o campos de búsqueda -->
    <form method="GET" action="{{ route('alumnos.index') }}" class="row g-2 mb-4">
        <div class="col">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" value="{{ request('nombre') }}">
        </div>
        <div class="col">
            <input type="text" name="apellido" class="form-control" placeholder="Apellido" value="{{ request('apellido') }}">
        </div>
        <div class="col">
            <input type="text" name="dni" class="form-control" placeholder="Documento" value="{{ request('dni') }}">
        </div>
        <div class="col">
            <select name="aula" class="form-select">
                <option value="">Aula</option>
                @foreach($aulas as $aula)
                    <option value="{{ $aula->id_aula }}" {{ request('aula') == $aula->id_aula ? 'selected' : '' }}>
                        {{ $aula->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </div>
    </form>

    <!-- Tabla de alumnos -->
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Documento</th>
                <th>Curso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->persona->nombre ?? '-' }}</td>
                    <td>{{ $alumno->persona->apellido ?? '-' }}</td>
                    <td>{{ $alumno->persona->documento ?? '-' }}</td>
                    <td>{{ $alumno->aula->nombre ?? 'Sin asignar' }}</td>
                    <td class="text-center">
                        <a href="{{ route('alumnos.show', $alumno->id_alumno) }}" class="btn btn-sm btn-info">👁</a>
                        <a href="{{ route('alumnos.edit', $alumno->id_alumno) }}" class="btn btn-sm btn-warning">✏️</a>
                        <form action="{{ route('alumnos.destroy', $alumno->id_alumno) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar alumno?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-muted">No hay alumnos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Volver</a>
    </div>
</div>
@section('contenido')

@endsection