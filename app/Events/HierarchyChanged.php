<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HierarchyChanged
{
    use Dispatchable, SerializesModels;

    public $type;      // 'section', 'block', 'level'
    public $action;    // 'created', 'updated', 'deleted'
    public $data;      // Datos relevantes del modelo

    /**
     * Create a new event instance.
     */
    public function __construct($type, $action, $data = [])
    {
        $this->type = $type;
        $this->action = $action;
        $this->data = $data;
    }
}
