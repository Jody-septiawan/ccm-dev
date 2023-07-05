<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Libs\FilesystemAdapter;

/**
 * Storage handler service.
 * Handle data according current app environment
 *
 * Production environment = Digital Ocean's space
 * Development/testing = Local storage
 *
 * @author Jody Septiawan <jody.septiawan5@gmail.com>
 */
class StorageService {

    /**
     * @var \Modules\CRM\Libs\FilesystemAdapter
     */
    protected $adapter;

    public function __construct()
    {
        /**
         * @var \Modules\CRM\Libs\FilesystemAdapter
         */
        $this->adapter = new FilesystemAdapter;
    }

    /**
     * Set filesystem env
     *
     * @author Jody Septiawan <jody.septiawan5@gmail.com>
     *
     * @param  string $env
     *
     * @return StorageService
     */
    public function setEnv($env)
    {
        $this->adapter->setEnv($env);
        return $this;
    }

    /**
     * Get storage from service.
     * Return Storage helper
     *
     * @author Jody Septiawan <jody.septiawan5@gmail.com>
     *
     * @return mixed
     */
    public function storage()
    {
        /**
         * Filesystem storage
         * @var string
         */
        $fs = $this->adapter->get();
        return Storage::disk('local');
    }

}
