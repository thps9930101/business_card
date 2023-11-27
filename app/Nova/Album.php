<?php

namespace App\Nova;

use App\Models\User;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Requests\NovaRequest;

class Album extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Album>
     */
    public static $model = \App\Models\Album::class;

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
            ID::make()->sortable()->hideFromIndex(),

            BelongsTo::make('用戶','user','App\Nova\User'),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make('狀態','type')->options([
                0 => '圖片',
                1 => '影片',
            ]),

            Image::make('封面照片','cover'),/* ->preview(function ($value) {
                return $value ? Storage::disk('s3')->temporaryUrl(
                    $value,
                    now()->addMinutes(5)
                ):null;
            }), */

            Select::make('狀態','status')->options([
                0 => '顯示',
                1 => '隱藏',
                2 => '已刪除',
            ]),

            Text::make('價格', 'price')
                ->sortable(),
            
            Boolean::make('發布','is_public')->default(0),

            HasMany::make('媒體','media','App\Nova\Media'),
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
        return '相簿管理';
    }

    // customize the singular label
    public static function singularLabel()
    {
        return '相簿';
    }
}
