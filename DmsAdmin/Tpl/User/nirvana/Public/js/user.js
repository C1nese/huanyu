        function showEditRename(obj){
            obj.style.border="2px solid #85B6E2";
            obj.nextSibling.style.display="";
        	obj.contentEditable=true;
        }
        function cancelEditRename(obj,obj1){
        	var pobj=obj.parentNode.previousSibling;
        	pobj.innerHTML=$("#"+obj1+"_v").html();
			if(pobj.innerHTML=="[无内容]"){
				pobj.style.opacity="0.5";
			}else{
				pobj.style.opacity="1.0";
			}
        	pobj.style.border="0px";
            pobj.nextSibling.style.display="none";
        	pobj.contentEditable=false;
        }
        function saveEditRename(obj,name){
        	var pobj=obj.parentNode.previousSibling;
        	
             //   $("#"+name+"_v").html(pobj.innerHTML);
        	   $.post("index.php?m=Common&a=saveEditRename",{name:name,value:pobj.innerHTML},function(data){
					pobj.innerHTML = data;
					$("#"+name+"_v").html(pobj.innerHTML);
					pobj.style.border="0px";
					if(data=="[无内容]"){
						pobj.style.opacity="0.5";
					}else{
						pobj.style.opacity="1.0";
					}
					pobj.nextSibling.style.display="none";
        			pobj.contentEditable=false;
				});
        }
		function ShowDialog(url,iHeight) {
			var iWidth = 330;
			var iTop=(window.screen.height-iHeight)/2;
			var iLeft=(window.screen.width-iWidth)/2;
			window.open(url,"","toolbar=no,location=no,direction=no,width="+iWidth+" ,Height="+iHeight+",top="+iTop+",left="+iLeft)
		}