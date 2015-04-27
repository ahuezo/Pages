<?php
namespace TypiCMS\Modules\Pages\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use TypiCMS\Models\Base;
use TypiCMS\Modules\History\Traits\Historable;
use TypiCMS\NestableTrait;
use TypiCMS\Presenters\PresentableTrait;

class Page extends Base
{

    use Historable;
    use Translatable;
    use PresentableTrait;
    use NestableTrait;

    protected $presenter = 'TypiCMS\Modules\Pages\Presenters\ModulePresenter';

    protected $fillable = array(
        'meta_robots_no_index',
        'meta_robots_no_follow',
        'position',
        'parent_id',
        'private',
        'is_home',
        'redirect',
        'css',
        'js',
        'module',
        'template',
        'image',
        // Translatable columns
        'title',
        'slug',
        'uri',
        'status',
        'body',
        'meta_keywords',
        'meta_description',
    );

    /**
     * Translatable model configs.
     *
     * @var array
     */
    public $translatedAttributes = array(
        'title',
        'slug',
        'uri',
        'status',
        'body',
        'meta_keywords',
        'meta_description',
    );

    protected $appends = ['status', 'title', 'thumb', 'uri'];

    /**
     * Columns that are file.
     *
     * @var array
     */
    public $attachments = array(
        'image',
    );

    /**
     * Get public uri
     *
     * @return string
     */
    public function getPublicUri($preview = false, $index = false, $lang = null)
    {
        if (! $this->hasTranslation($lang)) {
            return null;
        }

        $lang = $lang ? : config('app.locale') ;

        $indexUri = '';
        if (
            config('app.fallback_locale') != $lang ||
            config('typicms.main_locale_in_url')
        ) {
            $indexUri = '/' . $lang;
        }

        if (! $this->hasTranslation($lang)) {
            return $indexUri;
        }

        // If model is offline and we are not in preview mode
        if (! $preview && ! $this->translate($lang)->status) {
            return $indexUri;
        }

        if ($this->translate($lang)->uri) {
            return $indexUri . '/' . $this->translate($lang)->uri;
        }
    }

    public function uri($lang)
    {
        if (! $this->hasTranslation($lang)) {
            return null;
        }
        $uri = $this->translate($lang)->uri;
        if (
            config('app.fallback_locale') != config('app.locale') ||
            config('typicms.main_locale_in_url')
        ) {
            $uri = config('app.locale') . '/' . $uri;
        }
        return $uri;
    }

    /**
     * Get uri attribute from translation table
     *
     * @return string uri
     */
    public function getUriAttribute($value)
    {
        return $this->uri;
    }

    /**
     * A page can have menulinks
     */
    public function menulinks()
    {
        return $this->hasMany('TypiCMS\Modules\Menulinks\Models\Menulink');
    }

    /**
     * A page has many galleries.
     *
     * @return MorphToMany
     */
    public function galleries()
    {
        return $this->morphToMany('TypiCMS\Modules\Galleries\Models\Gallery', 'galleryable')
            ->withPivot('position')
            ->orderBy('position')
            ->withTimestamps();
    }

    /**
     * A page can have children
     */
    public function children()
    {
        return $this->hasMany('TypiCMS\Modules\Pages\Models\Page', 'parent_id')->order();
    }

    /**
     * A page can have a parent
     */
    public function parent()
    {
        return $this->belongsTo('TypiCMS\Modules\Pages\Models\Page', 'parent_id');
    }
}
