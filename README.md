
##包括3个类以及相应的example##


##如何跟CI一起工作##

#####全局自动加载#####
	**- autoload.php**
	
	      $autoload['libraries'] = array('curl');
		
	
	**- MY_Controller.php**
	
	    其他的控制器继承自MY_Controller


#####按需手动加载【一般在控制器里面添加】#####
    $this->load->library('curl');




##独立地工作##
    只要require就ok啦。。。