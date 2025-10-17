<?php

namespace App\Traits;

use DateTime;

trait PersonaTrait{

    /**Los modelos Eloquent no definen sus propiedades como variables explícitas (protected $nombre)
     * porque Laravel los gestiona dinámicamente desde la base de datos.
     * Cuando hacés $user->nombre, Laravel busca ese valor en el array interno $attributes. */
 

    public function getNombreCompleto(): string{
        return "{$this->nombre} {$this->apellido}";
    }

    public function getDni(): int{
        return(int) $this->dni;
    }

    public function getFechaNacimiento(): ?date{
        return $this->fechaNacimiento;
    }

    public function getDomicilio(): string{
        return $this->domicilio;
    }

    public function getNacionalidad(): string{
        return $this->nacionalidad;
    }

    public function getEdad(): int{
        if (!$this->fecha_nacimiento) return 0;

        $hoy = new DateTime();
        $nacimiento = new DateTime($this->fecha_nacimiento);
        return $hoy->diff($nacimiento)->y;
    }

    public function getDescripcion(): string{
        return "Persona: {$this->getNombreCompleto()}, DNI: {$this->dni}";
    }

}
