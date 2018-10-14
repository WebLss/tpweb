<?php
namespace Common\Model;
/**
* 项目内容model
*/
class ProjectArticleModel extends BaseModel{
    // 自动验证
    protected $_validate=array(
        array('tid','require','必须选择栏目'),
        array('title','require','项目标题必填'),
        array('author','require','作者必填'),
        array('content','require','项目内容必填'),
        );

    // 自动完成
    protected $_auto=array(
        array('click',0),
        array('is_delete',0),
        array('addtime','time',1,'function'),
        array('description','getDescription',3,'callback'),
        array('keywords','comma2coa',3,'callback'),
        );

    // 获得描述；供自动完成调用
    protected function getDescription($description){
        if(!empty($description)){
            return $description;
        }else{
            // 如果没有描述;则截取文章内容的前200字作为描述
            $description = preg_replace(array('/[~*>#-]*/', '/!?\[.*\]\(.*\)/', '/\[.*\]/'), '', $_POST['markdown']);
            return re_substr($description, 0, 200, true);
        }
    }

    // 顿号转换为英文逗号
    protected function comma2coa($keywords){
        return str_replace('、', ',', $keywords);
    }


    public function addData(){
        // 获取post数据
        $data=I('post.');
        // 给文章的插图添加水印;并取第一张图片作为封面图
        $data['cover'] = $this->getCover($data['markdown']);
        // 把markdown转html
        $data['html'] = markdown_to_html($data['markdown']);
        if($this->create($data)){
            if($aid=$this->add())
                return $aid;
        }
        return false;

    }

    /**
     * 给文章的插图添加水印;并取第一张图片作为封面图
     *
     * @param $content        markdown格式的文章内容
     * @param array $except   忽略加水印的图片
     * @return string
     */
    public function getCover($content, $except = [])
    {
        // 获取文章中的全部图片
        preg_match_all('/!\[.*\]\((\S*).*\)/i', $content, $images);
        if (empty($images[1])) {
            $cover = '/Upload/project/default/default.png';
        } else {
            // 添加水印
          /*  if(C('WATER_TYPE')!=0) {
                // 循环给图片添加水印
                foreach ($images[1] as $k => $v) {
                    add_water('.' . $v);

                    // 取第一张图片作为封面图
                    if ($k == 0) {
                        $cover = $v;
                    }
                }
            }*/
          $cover = $images[1][0];
        }
        return $cover;
    }


    public function editData(){
        // 获取post数据
        $data=I('post.');
        // 给文章的插图添加水印;并取第一张图片作为封面图
        $data['cover'] = $this->getCover($data['markdown']);
        // 把markdown转html
        $data['html'] = markdown_to_html($data['markdown']);
        if($this->create($data)){
            if($aid=$this->save())
                return $aid;
        }
        return false;
    }



    // 修改数据
    public function editData2(){
        // 获取post数据
        $data=I('post.');
        // 反转义为下文的 preg_replace使用
        $data['content']=htmlspecialchars_decode($data['content']);
        // 判断是否修改文章中图片的默认的alt 和title
        $image_title_alt_word=C('IMAGE_TITLE_ALT_WORD');
        if(!empty($image_title_alt_word)){
            // 修改图片默认的title和alt
            $data['content']=preg_replace('/title=\"(?<=").*?(?=")\"/','title="李胜松博客"',$data['content']);
            $data['content']=preg_replace('/alt=\"(?<=").*?(?=")\"/','alt="李胜松博客"',$data['content']);
        }
        // 将绝对路径转换为相对路径
        $data['content']=preg_replace('/src=\"^\/.*\/Upload\/image\/ueditor$/','src="/Upload/image/ueditor',$data['content']);
        $data['content']=htmlspecialchars($data['content']);
        if($this->create($data)){
            $aid=$data['aid'];
            $this->where(array('aid'=>$aid))->save();
            $image_path=get_ueditor_image_path($data['content']);
            D('ArticleTag')->deleteData($aid);
            if(isset($data['tids'])){
                D('ArticleTag')->addData($aid,$data['tids']);
            }
            // 删除图片路径
            D('ArticlePic')->deleteData($aid);
            if(!empty($image_path)){
                if(C('WATER_TYPE')!=0){
                    foreach ($image_path as $k => $v) {
                        add_water('.'.$v);
                    }
                }
                // 添加新图片路径
                D('ArticlePic')->addData($aid,$image_path);
            }
            return true;
        }else{
            return false;
        }
    }

    // 彻底删除
    public function deleteData(){
        $aid=I('get.aid',0,'intval');
        D('ArticlePic')->deleteData($aid);
        D('ArticleTag')->deleteData($aid);
        $this->where(array('aid'=>$aid))->delete();
        return true;
    }

    /**
     * 获得文章分页数据
     * @param strind $cid 分类id 'all'为全部分类
     * @param strind $is_show   是否显示 1为显示 0为不显示
     * @param strind $is_delete 状态 1为删除 0为正常
     * @param strind $limit 分页条数
     * @return array $data 分页样式 和 分页数据
     */
    public function getProjectPage($cid='all',$is_show='1',$is_delete=0,$limit=10){
        $field="aid,title,author,cover,keywords,description,is_show,is_delete,is_top,is_original,click,addtime,cid";
        if($cid=='all'){
            // 获取全部分类、全部标签下的文章
            if($is_show=='all'){
                $where=array(
                    'is_delete'=>$is_delete
                );
            }else{
                $where=array(
                    'is_delete'=>$is_delete,
                    'is_show'=>$is_show
                );
            }
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);

            $list=$this->field($field)
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'index',
                'id'=>0
            );

        }else{
            $where=array(
                'cid' => $cid,
                'is_delete'=>$is_delete,
                'is_show'=>$is_show
            );
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);

            $list=$this->field($field)
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'index',
                'id'=>0
            );
        }
        $show=$page->show();
        foreach ($list as $k => $v) {
            $list[$k]['addtime']=word_time($v['addtime']);
            $list[$k]['category']=current(D('ProjectCategory')->getDataByCid($v['cid'],'cid,cid,cname'));
            $list[$k]['url']=U('Home/Index/git/',array('aid'=>$v['aid']));
            $list[$k]['extend']=$extend;
        }
        $data=array(
            'page'=>$show,
            'data'=>$list,
        );
        return $data;

    }


    public function getNodePage($cid='all',$is_show=0,$is_delete=0,$limit=10){
        $field="aid,title,author,cover,keywords,description,is_show,is_delete,is_top,is_original,click,addtime,cid";
        if($cid=='all'){
            // 获取全部分类、全部标签下的文章
            if($is_show=='all'){
                $where=array(
                    'is_delete'=>$is_delete,
                    'is_show'=>0
                );
            }else{
                $where=array(
                    'is_delete'=>$is_delete,
                    'is_show'=>0
                );
            }
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);

            $list=$this->field($field)
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'index',
                'id'=>0
            );

        }else{
            $where=array(
                'cid' => $cid,
                'is_delete'=>$is_delete,
                'is_show'=>$is_show
            );
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);

            $list=$this->field($field)
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'index',
                'id'=>0
            );
        }
        $show=$page->show();
        foreach ($list as $k => $v) {
            $list[$k]['addtime']=word_time($v['addtime']);
            $list[$k]['category']=current(D('ProjectCategory')->getDataByCid($v['cid'],'cid,cid,cname'));
            $list[$k]['url']=U('Home/Index/git/',array('aid'=>$v['aid']));
            $list[$k]['extend']=$extend;
        }
        $data=array(
            'page'=>$show,
            'data'=>$list,
        );
        return $data;

    }


    /**
     * 获得文章分页数据
     * @param strind $cid 分类id 'all'为全部分类
     * @param strind $tid 标签id 'all'为全部标签
     * @param strind $is_show   是否显示 1为显示 0为不显示
     * @param strind $is_delete 状态 1为删除 0为正常
     * @param strind $limit 分页条数
     * @return array $data 分页样式 和 分页数据
     */
    public function getPageData2($cid='all',$tid='all',$is_show='1',$is_delete=0,$limit=10){
        if($cid=='all' && $tid=='all'){
            // 获取全部分类、全部标签下的文章
            if($is_show=='all'){
                $where=array(
                    'is_delete'=>$is_delete
                    );
            }else{
                $where=array(
                    'is_delete'=>$is_delete,
                    'is_show'=>$is_show
                    );
            }
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);
            $list=$this
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'index',
                'id'=>0
                );
        }elseif ($cid=='all' && $tid!='all') {
            // 获取tid下的全部文章
            if($is_show=='all'){
                $where=array(
                    'at.tid'=>$tid,
                    'a.is_delete'=>$is_delete
                    );
            }else{
                $where=array(
                    'at.tid'=>$tid,
                    'a.is_delete'=>$is_delete,
                    'a.is_show'=>$is_show
                    );
            }

        }elseif ($cid!='all' && $tid=='all') {
            // 获取cid下的全部文章
            if($is_show=='all'){
                $where=array(
                    'cid'=>$cid,
                    'is_delete'=>$is_delete,
                    );
            }else{
                $where=array(
                    'cid'=>$cid,
                    'is_delete'=>$is_delete,
                    'is_show'=>$is_show
                    );
            }
            $count=$this
                ->where($where)
                ->count();
            $page=new \Org\Bjy\Page($count,$limit);
            $list=$this
                ->where($where)
                ->order('addtime desc')
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
            $extend=array(
                'type'=>'cid',
                'id'=>$cid
                );
        }
        $show=$page->show();
        foreach ($list as $k => $v) {
            $list[$k]['addtime']=word_time($v['addtime']);
            $list[$k]['tag']=D('ArticleTag')->getDataByAid($v['aid'],'all');
            $list[$k]['pic_path']=D('ArticlePic')->getDataByAid($v['aid']);
            $list[$k]['category']=current(D('Category')->getDataByCid($v['cid'],'cid,cid,cname'));
            $v['content']=preg_ueditor_image_path($v['content']);
            $list[$k]['content']=htmlspecialchars($v['content']);
            $list[$k]['url']=U('Home/Index/article/',array('aid'=>$v['aid']));
            $list[$k]['extend']=$extend;
        }
        $data=array(
            'page'=>$show,
            'data'=>$list,
            );
        return $data;
    }


    // 传递aid获取单条全部数据 $map 主要为前台页面上下页使用
    public function getDataByAid($aid,$map=''){
        $categoryModel = D('ProjectCategory');
        if($map==''){
            // $map 为空则不获取上下篇文章
            $data=$this->where(array('aid'=>$aid))->find();
            $data['category']=current($categoryModel->getDataByCid($data['cid'],'cid,cid,cname,keywords'));
            // 获取相对路径的图片地址
            //$data['content']=preg_ueditor_image_path($data['content']);
        }else{
            if(isset($map['cid'])) {
                // 根据此分类cid获取上下篇文章
                $prev_map = $map;
                $prev_map[] = array('is_show' => 1);
                $prev_map[] = array('is_delete' => 0);
                $next_map = $prev_map;
                $prev_map['aid'] = array('gt', $aid);
                $next_map['aid'] = array('lt', $aid);
                $data['prev'] = $this->field('aid,title')->where($prev_map)->limit(1)->find();
                $data['next'] = $this->field('aid,title')->where($next_map)->order('aid desc')->limit(1)->find();
            }
            // 如果不为空 添加url
            if(!empty($data['prev'])){
                $data['prev']['url']=U('Home/Index/github/',array('aid'=>$data['prev']['aid']));
            }
            if(!empty($data['next'])){
                $data['next']['url']=U('Home/Index/github/',array('aid'=>$data['next']['aid']));
            }
            $data['current']=$this->where(array('aid'=>$aid))->find();
            $data['current']['category']=current($categoryModel->getDataByCid($data['current']['cid'],'cid,cid,cname,keywords'));
            //$data['current']['content']=preg_ueditor_image_path($data['current']['content']);
           // $data['current']['html']=preg_replace('/src=\"^\/.*\/Upload\/image\/editormd$/','src="/study_blog/Upload/image/editormd"',$data['current']['html']);
            $data['current']['html']=html_entity_decode( $data['current']['html']);
        }
        return $data;
    }



    // 传递aid获取单条全部数据 $map 主要为前台页面上下页使用
    public function getDataByAid2($aid,$map=''){
        if($map==''){
            // $map 为空则不获取上下篇文章
            $data=$this->where(array('aid'=>$aid))->find();
            $data['tids']=D('ArticleTag')->getDataByAid($aid);
            $data['tag']=D('ArticleTag')->getDataByAid($aid,'all');
            $data['category']=current(D('Category')->getDataByCid($data['cid'],'cid,cid,cname,keywords'));
            // 获取相对路径的图片地址
            $data['content']=preg_ueditor_image_path($data['content']);
        }else{
            if(isset($map['tid'])){
                // 根据此标签tid获取上下篇文章
                $prev_map['at.tid']=$map['tid'];
                $prev_map[]=array('a.is_show'=>1);
                $prev_map[]=array('a.is_delete'=>0);
                $next_map=$prev_map;
                $prev_map['a.aid']=array('gt',$aid);
                $next_map['a.aid']=array('lt',$aid);
                $data['prev']=$this->field('a.aid,a.title')->alias('a')->join('__ARTICLE_TAG__ at ON a.aid=at.aid')->where($prev_map)->limit(1)->find();
                $data['next']=$this->field('a.aid,a.title')->alias('a')->join('__ARTICLE_TAG__ at ON a.aid=at.aid')->where($next_map)->order('a.aid desc')->limit(1)->find();
            }else if(isset($map['cid'])){
                // 根据此分类cid获取上下篇文章
                $prev_map=$map;
                $prev_map[]=array('is_show'=>1);
                $prev_map[]=array('is_delete'=>0);
                $next_map=$prev_map;
                $prev_map['aid']=array('gt',$aid);
                $next_map['aid']=array('lt',$aid);
                $data['prev']=$this->field('aid,title')->where($prev_map)->limit(1)->find();
                $data['next']=$this->field('aid,title')->where($next_map)->order('aid desc')->limit(1)->find();
            }else{
                // 根据搜索词获取上下篇文章
                $prev_map=array('title'=>array('like','%'.$map['title'].'%'));
                $prev_map[]=array('is_show'=>1);
                $prev_map[]=array('is_delete'=>0);
                $next_map=$prev_map;
                $prev_map['aid']=array('gt',$aid);
                $next_map['aid']=array('lt',$aid);
                $data['prev']=$this->field('aid,title')->where($prev_map)->limit(1)->find();
                $data['next']=$this->field('aid,title')->where($next_map)->order('aid desc')->limit(1)->find();
            }
            // 如果不为空 添加url
            if(!empty($data['prev'])){
                $data['prev']['url']=U('Home/Index/article/',array('aid'=>$data['prev']['aid']));
            }
            if(!empty($data['next'])){
                $data['next']['url']=U('Home/Index/article/',array('aid'=>$data['next']['aid']));
            }
            $data['current']=$this->where(array('aid'=>$aid))->find();
            $data['current']['tids']=D('ArticleTag')->getDataByAid($aid);
            $data['current']['tag']=D('ArticleTag')->getDataByAid($aid,'all');
            $data['current']['category']=current(D('Category')->getDataByCid($data['current']['cid'],'cid,cid,cname,keywords'));
            $data['current']['content']=preg_ueditor_image_path($data['current']['content']);
        }
        return $data;
    }

    // 传递搜索词获取数据
    public function getDataByTitle($search_word){
        $map=array(
            'title'=>array('like',"%$search_word%")
            );
        $count=$this->where($map)->count();
        $page=new \Org\Bjy\Page($count,10);
        $list=$this
            ->where($map)
            ->order('addtime desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach ($list as $k => $v) {
            $list[$k]['pic_path']=D('ArticlePic')->getDataByAid($v['aid']);
            $list[$k]['url']=U('Home/Index/article/',array('search_word'=>$search_word,'aid'=>$v['aid']));
            $list[$k]['tids']=D('ArticleTag')->getDataByAid($v['aid']);
            $list[$k]['tag']=D('ArticleTag')->getDataByAid($v['aid'],'all');
            $list[$k]['category']=current(D('Category')->getDataByCid($v['cid'],'cid,cid,cname,keywords'));
            $list[$k]['addtime']=word_time($v['addtime']);
        }
        $show=$page->show();
        $data=array(
            'page'=>$show,
            'data'=>$list
            );
        return $data;
    }

    // 传递cid获得此分类下面的文章数据
    // is_all为true时获取全部数据 false时不获取is_show为0 和is_delete为1的数据
    public function getDataByCid($cid,$is_all=false){
        if($is_all){
            return $this->order('addtime desc')->select();
        }else{
            $where=array(
                'cid'=>$cid,
                'is_show'=>1,
                'is_delete'=>0,
            );
            return $this->where($where)->order('addtime desc')->select();
        }

    }

    // 传递$map获取count数据
    public function getCountData($map=array()){
        return $this->where($map)->count();
    }

}

