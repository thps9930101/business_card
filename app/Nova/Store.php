<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Store extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Store>
     */
    public static $model = \App\Models\Store::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
            //name
            Text::make('商店名稱','name')
                ->sortable()
                ->rules('required', 'max:255'),
            //address
            Text::make('商店地址','address')
                ->sortable()
                ->rules('required', 'max:255'),
            //phone
            Text::make('商店電話','phone')
                ->sortable()
                ->rules('required', 'max:255'),
            //tax id
            Text::make('統一編號','tax_id')
                ->sortable()
                ->rules('required', 'max:255'),
            HasMany::make('用戶','users','App\Nova\User'),
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
        return '商店管理';
    }

    // customize the singular label
    public static function singularLabel()
    {
        return '商店';
    }
}
