<?php
    /*RBAC权限控制类*/
    class Rbac{
        private $node_tablename; //定义私有属性节点表名称
        private $group_auth_tablename; //定义私有属性组权限表名称
        private $group_tablename; //定义私有属性用户组表名称
        private $group_user_tablename; //定义私有属性用户归属组表名称
        private $user_tablename; //定义私有属性用户表名称
        /*
        构造方法
        @param1 string 节点表名称
        @param2 string 用户权限表名称
        @param3 string 用户组表名称
        @param4 string 用户归属组表名称
        @param5 string 用户表名称
        */
        public function  __construct($node_tablename='node',$group_auth_tablename='group_auth',$group_tablename='group',$group_user_tablename='group_member',$user_tablename='member'){
            $this->node_tablename = $node_tablename; //获取节点表名称
            $this->group_auth_tablename = $group_auth_tablename; //获取用户权限表名称
            $this->group_tablename = $group_tablename; //获取用户组表名称
            $this->group_user_tablename = $group_user_tablename; //获取用户归属组表名称
            $this->user_tablename = $user_tablename; //获取用户表名称
        }
        /*
        设置节点方法
        @param1 string 节点名称
        @param2 string 节点父ID
        @param2 string 节点中文说明
        @return int 插入节点记录成功以后的ID
        */
        public function set_node($name,$pid,$zh_name=''){
            if(!empty($name) && !empty($pid)){
                $node = D($this->node_tablename)->insert(array("name"=>$name,"pid"=>$pid,"zh_name"=>$zh_name));
            }
            return $node;
        }
        /*
        设置权限方法
        @param1 int 组ID
        @param2 int 节点ID
        @return int 插入权限记录成功以后的ID
        */
        public function set_auth($gid,$nid){
            if(!empty($gid) && !empty($nid)){
                $auth = D($this->group_auth_tablename)->insert(array("gid"=>$gid,"nid"=>$nid));
            }
            return $auth;
        }
        /*
        获取节点方法
        @param1 int 节点ID
        @return array 获取到节点表的相关信息
        */
        public function get_node($id){
            if(!empty($id)){
                $data = D($this->node_tablename)->field("id,name,pid")->where(array('id'=>$id))->find();
                return $data;
            }else{
                return false;
            }
        }
        /*
        获取组权限方法
        @param1 int 用户组ID
        @return array 获取到组权限表的相关信息
        */
        public function get_auth($gid){
            if(!empty($gid)){
                $data = D($this->group_auth_tablename)->field("nid")->where(array('gid'=>$gid))->select();
                return $data;
            }else{
                return false;
            }
        }
        /*
        获取用户组方法
        @param1 int 用户ID
        @return array 获取该用户所对应的用户组id
        */
        public function get_group($uid){
            if(!empty($uid)){
                $data = D($this->group_user_tablename)->field("gid")->where(array('uid'=>$uid))->select();
                return $data;
            }else{
                return false;
            }
        }
        /*
        获取节点的子节点方法
        @param1 int 节点ID
        @return array 获取该节点所对应的全部子节点
        */
        public function get_cnode($nid){
            if(!empty($nid)){
                $cnode = D($this->node_tablename)->field("name")->where(array('pid'=>$nid))->select();
                return $cnode;
            }else{
                return false;
            }
        }
        /*
        获取权限方法
        @param1 int 用户ID
        @return array 得到权限列表
        */
        public function get_access($uid){
            if(!empty($uid)){
                //调用获取组信息方法
                $group = $this->get_group($uid);

                //遍历组信息
                foreach($group as $v){
                    //将组ID传入获取权限的方法
                    $auth = $this->get_auth($v['gid']); //获取该组的权限
                }

                //遍历该组的权限数组
                foreach($auth as $val){
                    //将节点的ID传入获取节点信息方法
                    $node[] = $this->get_node($val['nid']); //获取节点的相关信息
                }

                //遍历节点数组,并拼装
                foreach($node as $nval){
                    if($nval['pid']==0){
                        $fnode[] = $nval; //将控制器压入fnode数组
                        //$cnode = $this->get_cnode($nval['id']);
                    }else{
                        $cnode[] = $nval; //将控制器的方法压入cnode数组
                    }
                }

                //将控制器数组和控制器数组拼装成一个数组
                foreach($fnode as $fval){
                    foreach($cnode as $cval){
                        if($cval['pid'] == $fval['id']){
                            $access[$fval['name']][] = $cval['name'];
                        }
                    }
                }

                //返回权限列表数组
                return $access;
            }else{
                return false;
            }
        }

        /*
        检测权限方法
        @param1 int 用户ID
        @return boolean 权限禁止与否
        */
        public function check($uid){
            if(!empty($uid)){

                //将权限存入到$_SESSION['Access_List']中
                $_SESSION['Access_List'] = $this->get_access($uid);
                if(!empty($_GET['m'])){

                    //判断此控制器是否被允许
                    if(array_key_exists($_GET['m'],$_SESSION['Access_List'])){

                        //判断此控制器的方法是否被允许
                        if(in_array($_GET['a'],$_SESSION['Access_List'][$_GET['m']])){
                            //允许的话返回真
                            return true;
                        }else{
                            //否则返回假
                            return false;
                        }
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                //$_SESSION['user_'.$uid]['Access_List'] = 0;
                return false;
            }
        }


        public function show_node(){
            $path = APP_PATH.'/controls/';
            $handle = opendir($path);
            while(false!==($data = readdir($handle))){
                if(is_file($path.$data) && $data!='common.class.php' && $data!='pub.class.php'){
                    $controller = str_replace(".class.php",'',$data);
                    $res = fopen($path.$data,'r');
                    $str = fread($res,filesize($path.$data));
                    $pattern = '/function(.*)\(\)/iU';
                    preg_match_all($pattern, $str, $matches);
                    foreach($matches[1] as $v){
                        $v = trim($v);
                        $arr[$controller][] = $v;
                    }
                }
            }
            closedir($handle);
            return $arr;
        }

    }


    //初始化类：
    <?php
    /*初始化控制器*/
    class Common extends Action {
        /*
        初始化方法
        */
        public function init(){
            //如果SESSION为空，则跳转
            if(empty($_SESSION['user_login'])){
            $this->redirect("pub/index");
            }
            $a = new rbac();
            if(!$a->check($_SESSION['user_info']['id'])){
                echo "<script>alert('您没有此权限!')</script>";
                exit("<script>document.write('<span style=\'font-size:40px;font-weight:bold\'>Access Forbidden');alert('您没有此权限!');</script>");
                $this->redirect("pub/index");
            }
        }
    }