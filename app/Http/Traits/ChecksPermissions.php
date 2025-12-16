<?php

namespace App\Http\Traits;

trait ChecksPermissions
{
    /**
     * Check if user can edit/create data.
     */
    protected function checkCanEdit(): void
    {
        if (!auth()->user()->canEdit()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data.');
        }
    }

    /**
     * Check if user can delete data.
     */
    protected function checkCanDelete(): void
    {
        if (!auth()->user()->canDelete()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data.');
        }
    }
}
