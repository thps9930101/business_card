<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

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
        'id',
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

            //belongs to store
            BelongsTo::make('商店','store','App\Nova\Store')
            ->nullable(),//show store name

            Text::make('價格', 'costs')
                ->sortable(),

            Select::make('狀態','type')->options([
                0 => '單張',
                1 => '相簿',
            ])
            ->default(0)
            ->displayUsingLabels()
            ->withMeta(['value' => $this->status]),

            
            BelongsTo::make('媒體','media','App\Nova\Media')->nullable(),

            BelongsTo::make('相簿','album','App\Nova\Album')->nullable(),

            Boolean::make('是否上架', 'is_activated')
                ->sortable(),

            Select::make('狀態','status')->options([
                0 => '未處理',
                1 => '已處理',
                2 => '已完成',
                3 => '已取消',
            ])
            ->default(0)
            ->displayUsingLabels()
            ->withMeta(['value' => $this->status]),

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

    // customize the label
    public static function label()
    {
        return '廠商商品管理';
    }

    // customize the singular label
    public static function singularLabel()
    {
        return '廠商商品';
    }
}
