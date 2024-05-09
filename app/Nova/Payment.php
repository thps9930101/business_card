<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class Payment extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Payment>
     */
    public static $model = \App\Models\Payment::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'status'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            //user id
            BelongsTo::make('用戶', 'user', 'App\Nova\User')->nullable(),

            BelongsTo::make('商品方案ID', 'product_solution','App\Nova\Product_solution'),

            Select::make('支付方式', 'payment_method')->options([
                'paypal' => 'paypal',
            ])
            ->default(0)
            ->displayUsingLabels()
            ->withMeta(['value' => $this->status]),

            Select::make('事件類型', 'event_type')->options([
                'CHECKOUT.ORDER.APPROVED' => 'CHECKOUT.ORDER.APPROVED',
                'PAYMENT.CAPTURE.COMPLETED' => 'PAYMENT.CAPTURE.COMPLETED',
                'PAYMENT.CAPTURE.REFUNDED' => 'PAYMENT.CAPTURE.REFUNDED',
                'CUSTOMER.DISPUTE.CREATED' => 'CUSTOMER.DISPUTE.CREATED',
                'CUSTOMER.DISPUTE.UPDATED' => 'CUSTOMER.DISPUTE.UPDATED',
                'CUSTOMER.DISPUTE.RESOLVED' => 'CUSTOMER.DISPUTE.RESOLVED'
            ])
            ->default(1)
            ->displayUsingLabels()
            ->withMeta(['value' => $this->status]),

            Text::make('金額', 'payment_amount')->sortable(),

            Select::make('貨幣', 'payment_currency')->options([
                'USD' => 'USD',
                'TWD' => 'TWD',
            ])
            ->default(0)
            ->displayUsingLabels()
            ->withMeta(['value' => $this->status]),

            Text::make('交易序號', 'transaction_id')->sortable(),

            Text::make('狀態', 'status')->sortable(),

            Text::make('原因', 'summary')->sortable()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }


    //customize the label
    public static function label()
    {
        return '支付管理';
    }

    //customize the singular label
    public static function singularLabel()
    {
        return '支付';
    }
}
