<?php

namespace App\Traits;

trait ChecksPermissions
{
    /**
     * Verifica si el usuario tiene el permiso especificado
     *
     * @param string $permission El permiso a verificar
     * @param bool $throwError Si es true, lanza un error 403
     * @return bool
     */
    protected function checkPermission(string $permission, bool $throwError = false): bool
    {
        $hasPermission = auth()->user()->can($permission);
        
        if (!$hasPermission && $throwError) {
            abort(403, 'You do not have permission to perform this action');
        }
        
        return $hasPermission;
    }
    
    /**
     * Verifica el permiso y muestra un mensaje de error
     *
     * @param string $permission El permiso a verificar
     * @param string $errorMessage Mensaje de error a mostrar
     * @return bool
     */
    protected function checkPermissionWithMessage(string $permission, string $errorMessage): bool
    {
        $hasPermission = auth()->user()->can($permission);
        
        if (!$hasPermission) {
            session()->flash('error', $errorMessage);
        }
        
        return $hasPermission;
    }
} 