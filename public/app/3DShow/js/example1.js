window.onload = function() {
	var list = document.getElementById('myList');
	var listChild = document.getElementsByTagName('li');
	var id = getnavivalues()["case_id"];

	if(id != undefined && id != null) {
		var url = "https://www.homeeyes.cn/api/artDetail";
		var param = "case_id=" + id;
		
		ajax_method(url, param, "post", function(data) {
		
			var images = data.data.images;
			
			if(images != undefined && images.length > 0) {
				setImg(images[0]);
				list.innerHTML = "";
				for(var i = 0; i < images.length; i++) {
					var para = document.createElement("li");
					var img = document.createElement("img");
					img.src = images[i];
					img.className = "meun_img";
					img.addEventListener('click', function() {
						var url = this.src;
						console.log(url)
						setImg(url);

					}, false);
					para.appendChild(img);
					list.appendChild(para);

				}
			} else {
				loadPredefinedPanorama();
			}
		});
	} else {
		for(var i = 0; i < listChild.length; i++) {
			listChild[i].addEventListener('click', function() {
				var url = this.children[0].currentSrc;
				console.log(url)
				setImg(url);

			}, false);
		}
		loadPredefinedPanorama();
	}

	var clickfile = document.getElementById("clickfile");
	clickfile.addEventListener('click', clickFile, false);
	document.getElementById('pano').addEventListener('change', upload, false);
};

function clickFile() {
	document.getElementById('pano').click()
}
// Load the predefined panorama
function loadPredefinedPanorama(evt) {
	if(evt != undefined) {
		evt.preventDefault();
	}
	setImg("img/banner1.jpg");
}

function setImg(url) {
	var height = document.documentElement.clientHeight + "px";
	var div = document.getElementById('container');
	var PSV = new PhotoSphereViewer({
		// Path to the panorama
		panorama: url,

		// Container
		container: div,

		// Deactivate the animation
		time_anim: false,

		// Display the navigation bar
		navbar: true,

		// Resize the panorama
		size: {
			width: '100%',
			height: height
		}
	});
}
// Load a panorama stored on the user's computer
function upload() {
	// Retrieve the chosen file and create the FileReader object
	var file = document.getElementById('pano').files[0];
	var reader = new FileReader();
	console.log(file);
	reader.onload = function() {
		setImg(reader.result);

	};

	reader.readAsDataURL(file);
}

function getnavivalues() {
	var url = location.search; //获取url中"?"符后的字串  
	var theRequest = new Array();
	if(url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i++) {
			theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}

function ajax_method(url, data, method, success) {
	// 异步对象
	var ajax = new XMLHttpRequest();

	// get 跟post  需要分别写不同的代码
	if(method == 'get') {
		// get请求
		if(data) {
			// 如果有值
			url += '?';
			url += data;
		} else {

		}
		// 设置 方法 以及 url
		ajax.open(method, url);

		// send即可
		ajax.send();
	} else {
		// post请求
		// post请求 url 是不需要改变
		ajax.open(method, url);

		// 需要设置请求报文
		ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		// 判断data send发送数据
		if(data) {
			// 如果有值 从send发送
			ajax.send(data);
		} else {
			// 木有值 直接发送即可
			ajax.send();
		}
	}

	// 注册事件
	ajax.onreadystatechange = function() {
		// 在事件中 获取数据 并修改界面显示
		if(ajax.readyState == 4 && ajax.status == 200) {
			// console.log(ajax.responseText);

			// 将 数据 让 外面可以使用
			// return ajax.responseText;

			// 当 onreadystatechange 调用时 说明 数据回来了
			// ajax.responseText;

			// 如果说 外面可以传入一个 function 作为参数 success
			var obj = JSON.parse(ajax.responseText);
			success(obj);
		}
	}

}