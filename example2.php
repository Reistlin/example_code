<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class WaiterOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $me = \Auth::user()->load('clients');

        $clients = $me->clients->pluck('id');
        return \App\Order::where(function($query) use($clients, $me) { $query->whereIn('client_id', $clients)->orWhere('admin_id', $me->my_admin->first()['id']); })
            ->where(function ($query) use($me) { $query->where('waiter_id', $me->id)->orWhereNull('waiter_id'); })
            ->actual()
            ->orderBy('id', 'desc')
            ->get()
            ->load('client', 'items.food')
            ->groupBy('client_id');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(\App\Order $order)
    {
        $me = \Auth::user();
        $client_id = $order->client_id;


        $my_client = $me->clients->where('id', $client_id);

        if(!$my_client->isEmpty()) {
            return $order->client->orders()->actual()->orderBy('id', 'desc')->get()->load('items.food');
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $me = \Auth::user();
        $items = $request->get('items');
        $client_id = $request->get('client_id');

        $client = $me->clients()->where('id', $client_id)->first();
        if(!$client) {
            return response(['not your client', 433]);
        }

        if($items) {
            $order = \App\Order::actual()->where('client_id', '=', $client_id)->orderBy('id', 'desc')->first();
            if(!$order) {
                $order = new \App\Order;

                $order->client()->associate($client_id);
                $order->waiter()->associate($me);
                $order->status = 1;
                $order->save();
            }
            foreach ($items as $item) {
                $food = \App\Food::find($item['food']);
                if($food->category->menu->admin_id == $me->admin_id) {

                    $OrderItem = new \App\OrderItem($item);

                    $OrderItem->food()->associate($food);
                    $OrderItem->order()->associate($order);
                    $OrderItem->price = $food->price;
                    $OrderItem->status = 0;
                    $OrderItem->save();
                }
            }
            foreach ($me->cooks()->online()->get() as $cook) {
                \App\Notification::add([$OrderItem], 'new_item', $cook);
            }
            return $order->load('items.food');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, \App\Order $order)
    {
        $me = \Auth::user();
        $client_id = $order->client_id;
        $my_client = $me->clients->where('id', $client_id);
        if(!$my_client->isEmpty() || !$client_id) {
            $status = $request->get('status');

            $orders_type = $status == 1 ? 'new' : 'actual';
            if(!$my_client->isEmpty())
                $orders = $order->client->orders()->{$orders_type}()->get();
            else {
                $phone = $request->get('phone');
                $orders = \App\Order::where('phone', $phone)->{$orders_type}()->get();
            }
            if(!$orders->isEmpty()) {
                $first_order = $orders->first();
                $is_not_assigned = !$first_order->waiter_id;

                if($first_order->waiter_id && $first_order->waiter_id != $me->id) {
                    return response(['client busy'], 431);
                }

                foreach ($orders as $o) {
                    if($status == 1) {
                        if(!$o->waiter_id) {
                            $o->waiter()->associate($me);
                        }
                    }

                    if($status==2 && $o->status!=1) {
                        $o->delete();
                        continue;
                    }

                    $o->status = $status;
                    $o->save();
                }

                if($status == 1) {
                    if($is_not_assigned) {
                        \App\Notification::add($me->toArray(), 'ur_mine', $order->client);
                        foreach ($order->client->waiters()->online()->get() as $waiter) {
                            if($waiter->id == $me->id)
                                continue;
                            \App\Notification::add($order->client->toArray(), 'hes_mine', $waiter);
                        }
                    } else {
                        \App\Notification::add($orders->toArray(), 'accept', $order->client);
                    }

                }

                if($status == 2) {
                    \App\Notification::add($me->toArray(), 'done', $order->client);
                }

                $online_cooks = $me->cooks()->online()->get();

                $orders->load(['items' => function($query) {
                    $query->new();
                }, 'items.food']);

                $items = [];

                foreach ($orders as $o) {
                    foreach ($o->items as $item) {
                        array_push($items, $item);
                    }
                }
                if($items) {
                    foreach ($online_cooks as $cook) {
                        \App\Notification::add($items, $status == 1 ? 'new_item' : 'item_canceled', $cook);
                    }
                }
            } else {
                return response(['client busy'], 431);
            }

            return $orders;
        }
    }
}
