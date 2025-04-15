<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Settings extends Model
{
    use HasFactory;

    /**
     * The User instance.
     *
     * @var User
     */
    protected $user;

    /**
     * The list of settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Create a new settings instance.
     */
    public function __construct(array $settings, User $user)
    {

        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * Retrieve the given setting.
     *
     * @param  string  $key
     * @return string
     */
    public function get($key)
    {

        return Arr::get($this->settings, $key);
    }

    /**
     * Create and persist a new setting.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public function set($key, $value)
    {

        $this->settings[$key] = $value;

        $this->persist();
    }

    /**
     * Determine if the given setting exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Retrieve an array of all settings.
     *
     * @return array
     */
    public function allSettings()
    {
        return $this->settings;
    }

    /**
     * Merge the given attributes with the current settings.
     * But do not assign any new settings.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function merge($key, $value)
    {

        $this->settings = Arr::add($this->settings, $key, $value);

        return $this->persist();
    }

    /**
     * Persist the settings.
     *
     * @return mixed
     */
    protected function persist()
    {

        return $this->user->update(['settings' => $this->settings]);
    }

    /**
     * Magic property access for settings.
     *
     * @param  string  $key
     *
     * @throws Exception
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        throw new Exception("The {$key} setting does not exist.");
    }
}
