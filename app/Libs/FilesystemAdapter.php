<?php

namespace App\Libs;

/**
 * Filesystem adapter
 *
 * @author Jody Septiawan <jody.septiawan5@gmail.com>
 */
class FilesystemAdapter implements InterfaceFilesystemAdapter {

    /**
     * Filesystem / storage type
     *
     * @var string
     */
    protected $fs;

    /**
     * Environment
     *
     * @var string
     */
    protected $env;

    public function __construct()
    {
        $this->env = app()->environment();
    }

    /**
     * Set environment
     *
     * @author Jody Septiawan <jody.septiawan5@gmail.com>
     *
     * @param  string $env
     *
     * @return FilesystemAdapter
     */
    public function setEnv(String $env)
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Get filesystem
     *
     * @author Jody Septiawan <jody.septiawan5@gmail.com>
     *
     * @return string
     */
    public function get()
    {
        if ($this->env == 'production') {
            $fs = 'spaces';
        } else {
            $fs = 'public';
        }
        $this->fs = $fs;
        return $this->fs;
    }

}
