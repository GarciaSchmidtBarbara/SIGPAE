<?php

namespace App\Services\Interfaces;

use App\Models\Familiar;
use Illuminate\Database\Eloquent\Collection;

interface FamiliarServiceInterface
{
    public function getAllFamiliares(): Collection;
    public function getFamiliarById(int $id): ?Familiar;
    public function createFamiliar(array $data): Familiar;
    public function updateFamiliar(int $id, array $data): Familiar;
    public function deleteFamiliar(int $id): bool;
    public function getFamiliarWithPersona(int $id): ?Familiar;
    public function getAllFamiliaresWithPersona(): Collection;
}