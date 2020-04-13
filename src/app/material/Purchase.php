<?php

namespace app\material;

use db\SqlMapper;

class Purchase extends Index
{
    function get(\Base $f3)
    {
        $data = [];
        $material = new SqlMapper('virgo_material');
        $material->load(['id in (' . $f3->get('GET.id') . ')']);
        while (!$material->dry()) {
            $data[] = $material->cast();
            $material->next();
        }
        $f3->set('data', $data);
        echo \Template::instance()->render('material/purchase.html');
    }

    function post(\Base $f3)
    {
        $id = explode(',', $f3->get('POST.id'));
        $quantity = explode(',', $f3->get('POST.quantity'));
        $plan = array_combine($id, $quantity);
        $order = new SqlMapper('virgo_material_order');
        $material = new SqlMapper('virgo_material');
        $material->load(['id in (' . $f3->get('POST.id') . ')']);
        while (!$material->dry()) {
            $order->reset();
            $order->copyfrom([
                'serial' => $f3->get('POST.serial'),
                'status' => 0,
                'name' => $material['name'],
                'type' => $material['type'],
                'plan_price' => $material['price'],
                'plan_quantity' => $plan[$material['id']],
                'plan_supplier' => $material['supplier'],
                'final_price' => $material['price'],
                'final_quantity' => $plan[$material['id']],
                'final_supplier' => $material['supplier'],
            ]);
            $order->save();
            $material->next();
        }
        header('location:' . $this->url('/material/Order'));
    }
}
