<?php

namespace App\Services;

use App\Models\AthleteGuardianModel;
use App\Models\GuardianModel;
use CodeIgniter\I18n\Time;

class AthleteGuardianService
{
    protected AthleteGuardianModel $links;
    protected GuardianModel $guardians;

    public function __construct()
    {
        $this->links = new AthleteGuardianModel();
        $this->guardians = new GuardianModel();
    }

    public function listByAthlete(int $athleteId): array
    {
        return $this->links->builder()
            ->select('athlete_guardians.*, guardians.full_name, guardians.phone, guardians.email')
            ->join('guardians', 'guardians.id = athlete_guardians.guardian_id', 'left')
            ->where('athlete_guardians.athlete_id', $athleteId)
            ->orderBy('athlete_guardians.is_primary', 'DESC')
            ->orderBy('athlete_guardians.id', 'DESC')
            ->get()->getResultArray();
    }

    public function listByGuardian(int $guardianId): array
    {
        return $this->links->builder()
            ->select('athlete_guardians.*, athletes.first_name, athletes.last_name')
            ->join('athletes', 'athletes.id = athlete_guardians.athlete_id', 'left')
            ->where('athlete_guardians.guardian_id', $guardianId)
            ->orderBy('athlete_guardians.id', 'DESC')
            ->get()->getResultArray();
    }

    public function link(int $athleteId, int $guardianId, int $isPrimary = 0, string $notes = null): int
    {
        $existing = $this->links
            ->where('athlete_id', $athleteId)
            ->where('guardian_id', $guardianId)
            ->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        if ($isPrimary === 1) {
            $this->unsetPrimary($athleteId);
        }

        return (int) $this->links->insert([
            'athlete_id'  => $athleteId,
            'guardian_id' => $guardianId,
            'is_primary'  => $isPrimary,
            'notes'       => $notes,
            'created_at'  => Time::now()->toDateTimeString(),
        ]);
    }

    public function updateLink(int $id, int $athleteId, int $isPrimary = 0, string $notes = null): bool
    {
        if ($isPrimary === 1) {
            $this->unsetPrimary($athleteId);
        }

        return $this->links->update($id, [
            'is_primary' => $isPrimary,
            'notes'      => $notes,
        ]);
    }

    public function unlink(int $id): bool
    {
        return $this->links->delete($id);
    }

    public function findLink(int $id): array
    {
        return $this->links->find($id);
    }

    protected function unsetPrimary(int $athleteId): void
    {
        $this->links->where('athlete_id', $athleteId)->set(['is_primary' => 0])->update();
    }
}
