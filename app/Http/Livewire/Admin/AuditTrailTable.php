<?php

namespace App\Http\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\LoginAttempt;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTrailTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = 'audit';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function render()
    {
        $query = $this->type === 'login' ? LoginAttempt::query() : AuditLog::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('action', 'like', "%{$this->search}%")
                    ->orWhere('ip_address', 'like', "%{$this->search}%")
                    ->orWhere('user_agent', 'like', "%{$this->search}%");
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return view('livewire.admin.audit-trail-table', [
            'entries' => $query->latest()->paginate(20),
        ]);
    }
}
