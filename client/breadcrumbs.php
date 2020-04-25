<?php

namespace modules\breadcrumbs\client;

use libraries\helper\html;
use libraries\helper\url;
use m\core;
use m\i18n;
use m\module;
use m\registry;
use m\view;

class breadcrumbs extends module {

    public static $_name = '*Breadcrumbs*';

    protected $css = ['/css/breadcrumbs.css'];

    public function _init()
    {
        $breadcrumbs = (array)registry::get('breadcrumbs');

        if (empty($breadcrumbs) || !isset($this->view->{$this->module_name})) {
            return false;
        }

        $breadcrumbs_json = [];

        $root_path = $this->get->controller == 'admin' ? '/' . $this->config->admin_panel_alias : '/';

        $arr = [html::a(url::to($root_path), '', ['class' => 'home'])];

        $n = 1;

        $breadcrumbs_json[] = (object)[
            '@type' => 'ListItem',
            'position' => $n,
            'item' => (object)[
                '@id' => url::to($root_path, null, true),
                'name' => '*Home*',
            ],
        ];

        foreach ($breadcrumbs as $k => $v) {
            $arr[] = html::a(url::to($k), $v);

            $n++;

            $breadcrumbs_json[] = (object)[
                '@type' => 'ListItem',
                'position' => $n,
                'item' => (object)[
                    '@id' => url::to($k, null, true),
                    'name' => $v,
                ],
            ];
        }

        if (empty($this->page)) {
            $title = '';
        }
        else if (!empty($this->page->seo) && !empty($this->page->seo->title)) {
            $title = $this->page->seo->title;
        }
        else {
            $title = $this->page->name;
        }

        if (registry::get('title')) {
            $title = registry::get('title');
        }

        $breadcrumbs_json = (object)[
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs_json,
        ];

        $application_ld_json = '<script type="application/ld+json">' . json_encode($breadcrumbs_json, JSON_UNESCAPED_UNICODE) . '</script>';

        return view::set($this->module_name, $this->view->{$this->module_name}->prepare([
            'links' => implode('', $arr),
            'title' => $title,
        ]) . stripslashes($application_ld_json));
    }
}