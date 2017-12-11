<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use QL\QueryList;
use QL\Ext\PhantomJs;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    private $page = 0;
    private $pages = [];
    public function index()
    {
        $html = file_get_contents('https://laravel-china.org/topics');
        $this->pages = $this->getPages($html);
        foreach($this->pages as $v){
            $this->page++;
            $this->getData($v['url']);
        }
        
    }

    public function getPages($html){
        return QueryList::html($html)->rules([
            'url' => ['.pagination a','href']
        ])->query()->getData()->unique();
    }

    public function getData($url){
        $ql = QueryList::get($url)->rules([
            'title'=>array('.media-heading>a','text'),
            'image'=>array('.avatar img','src'),
            'link'=>array('.media-heading>a','href')
        ]);
        $data = $ql->query()->getData();
        foreach($data as $v){
            echo $this->saveFile($v['image']).'<br/>>';
        }
    }

    public function saveFile($url)
    {
        $file = file_get_contents($url);
        $fileinfo = parse_url($url)['path'];
        $name = '/uploads/picture/' . $this->page . '/' . time() . uniqid() . '.' . collect(explode('.', $fileinfo))->last();
        if (\Storage::put($name, $file)) {
            return $name;
        }
    }
}
