<?php

namespace App\Nova;

use App\Models\Order;
use App\Models\User;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class Media extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Media>
     */
    public static $model = \App\Models\Media::class;

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
        $order = $this->order;
        $user = $this->user;
        return [
            ID::make()->sortable(),
            //user id
            BelongsTo::make('訂單','order','App\Nova\Order'),
            BelongsTo::make('用戶','user','App\Nova\User')->default(Order::find($request->viaResourceId)?->user_id),

            Select::make('類型','type')->options([
                0 => '影片',
                1 => '照片',
            ])
            ->displayUsingLabels()
            ->default($this->type??0)
            ->rules('required','integer'),

            Select::make('狀態','status')->options([
                0 => '尚未處理',
                1 => '已經處理',
            ])->displayUsingLabels()
            ->default($this->status??0)
            ->rules('required','integer'),
            Image::make('封面照片','cover')->preview(function ($value) {
                return $value ? Storage::disk('s3')->temporaryUrl(
                    $value,
                    now()->addMinutes(5)
                ):null;
            })
            ->path(env('APP_ENV')."/$user?->id/$order?->id/$this?->id/cover")
            ->hideFromIndex()
            ->hideWhenCreating(),
            File::make('影片','obj')
            ->path(env('APP_ENV')."/$user?->id/$order?->id/$this?->id/obj")
            ->hideWhenCreating(),


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
           return '影片管理';
       }

       // customize the singular label
       public static function singularLabel()
       {
           return '影片';
       }
}
