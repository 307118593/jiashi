function Request() {
	this.url = "http://www.zhenxc.com/feesys";
	this.urled = "https://www.zhenxueche.com/umbra-mobile";
	this.getopenid = function() {
		var request = {
			QueryString: function(val) {
				var uri = window.location.search;
				var re = new RegExp("" + val + "=([^\&\?]*)", "ig");
				return((uri.match(re)) ? (uri.match(re)[0].substr(val.length + 1)) : null);
			}
		};
		var openid = request.QueryString("openid");
		var openidlo = localStorage.getItem("openid");
		openid = openid == null ? openidlo : openid;
		if(openid == null) { //如果本地或者url无openid，则微信获取
			var url = encodeURI("http://www.zhenxc.com/feesys/user/login?url=" + location.href);
			location.href = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx466091d30ae38b09&redirect_uri=" + url + "&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
		} else {
			localStorage.setItem("openid", openid);
		}
		return openid;
	};
	this.request = function(url, para, type, success, error) {
		$.ajax({
			type: type,
			url: url,
			data: para,
			dataType: "json",
			timeout: 60000,
			async: false,
			success: success,
			error: error
		});

	};
	this.requestget = function(url, type, success, error) {
		$.ajax({
			type: type,
			url: url,
			dataType: "json",
			timeout: 60000,
			success: success,
			error: error
		});

	};
	this.getnavivalues = function() {
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

}