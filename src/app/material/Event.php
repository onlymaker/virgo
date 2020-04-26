<?php

namespace app\material;

use db\SqlMapper;

class Event extends \Prefab
{
    function upload(array $data) {
        $history = new SqlMapper('virgo_material_history');
        $history['name'] = $data['name'];
        $history['type'] = $data['type'];
        $history['event'] = $this->toJson([
            'name' => 'upload',
        ]);
        $history['current'] = $this->toJson($data);
        $history->save();
    }

    function update(array $prev, array $current) {
        $history = new SqlMapper('virgo_material_history');
        $history['name'] = $current['name'];
        $history['type'] = $current['type'];
        $history['event'] = $this->toJson([
            'name' => 'update',
        ]);
        $history['previous'] = $this->toJson($prev);
        $history['current'] = $this->toJson($current);
        $history->save();
    }

    function delete(array $current)
    {
        $history = new SqlMapper('virgo_material_history');
        $history['name'] = $current['name'];
        $history['type'] = $current['type'];
        $history['event'] = $this->toJson([
            'name' => 'delete',
        ]);
        $history['previous'] = $this->toJson($current);
        $history->save();
    }

    function purchase(array $prev, array $order) {
        $history = new SqlMapper('virgo_material_history');
        $history['name'] = $order['name'];
        $history['type'] = $order['type'];
        $history['event'] = $this->toJson([
            'name' => 'purchase',
            'serial' => $order['serial']
        ]);
        $history['previous'] = $this->toJson($prev);
        $prev['quantity'] += $order['final_quantity'];
        $prev['price'] = $order['final_price'];
        $prev['supplier'] = $order['final_supplier'];
        $history['current'] = $this->toJson($prev);
        $history->save();
    }

    function usage(array $current, string $order, int $quantity)
    {
        $history = new SqlMapper('virgo_material_history');
        $history['name'] = $current['name'];
        $history['type'] = $current['type'];
        $history['event'] = $this->toJson([
            'name' => 'usage',
            'serial' => $order
        ]);
        $history['previous'] = $this->toJson($current);
        $current['quantity'] -= $quantity;
        $history['current'] = $this->toJson($current);
        $history->save();
    }

    private function toJson(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
