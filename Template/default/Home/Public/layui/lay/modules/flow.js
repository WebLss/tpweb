"use strict";layui.define("jquery",function(e){var l=layui.jquery,o=function(e){};o.prototype.load=function(e){var o,t,i,n,r=this,a=0;e=e||{};var c=l(e.elem);if(c[0]){var u=l(e.scrollElem||document),f=e.mb||50,m=!("isAuto"in e)||e.isAuto,s=e.end||"没有更多了",y=e.scrollElem&&e.scrollElem!==document,v="<cite>加载更多</cite>",d=l('<div class="layui-flow-more"><a href="javascript:;">'+v+"</a></div>");c.find(".layui-flow-more")[0]||c.append(d);var h=function(e,n){e=l(e),d.before(e),n=0==n||null,n?d.html(s):d.find("a").html(v),t=n,o=null,i&&i()},p=function(){o=!0,d.find("a").html('<i class="layui-anim layui-anim-rotate layui-anim-loop layui-icon ">&#xe63e;</i>'),"function"==typeof e.done&&e.done(++a,h)};if(p(),d.find("a").on("click",function(){l(this),t||o||p()}),e.isLazyimg)var i=r.lazyimg({elem:e.elem+" img",scrollElem:e.scrollElem});return m?(u.on("scroll",function(){var e=l(this),i=e.scrollTop();n&&clearTimeout(n),t||(n=setTimeout(function(){var t=y?e.height():l(window).height();(y?e.prop("scrollHeight"):document.documentElement.scrollHeight)-i-t<=f&&(o||p())},100))}),r):r}},o.prototype.lazyimg=function(e){var o,t=this,i=0;e=e||{};var n=l(e.scrollElem||document),r=e.elem||"img",a=e.scrollElem&&e.scrollElem!==document,c=function e(l,o){var r=n.scrollTop(),c=r+o,e=a?function(){return l.offset().top-n.offset().top+r}():l.offset().top;if(e>=r&&e<=c&&!l.attr("src")){var f=l.attr("lay-src");layui.img(f,function(){var e=t.lazyimg.elem.eq(i);l.attr("src",f).removeAttr("lay-src"),e[0]&&u(e),i++})}},u=function e(o,u){var e=a?(u||n).height():l(window).height(),f=n.scrollTop(),m=f+e;if(t.lazyimg.elem=l(r),o)c(o,e);else for(var s=0;s<t.lazyimg.elem.length;s++){var y=t.lazyimg.elem.eq(s),v=a?function(){return y.offset().top-n.offset().top+f}():y.offset().top;if(c(y,e),i=s,v>m)break}};if(u(),!o){var f;n.on("scroll",function(){var e=l(this);f&&clearTimeout(f),f=setTimeout(function(){u(null,e)},50)}),o=!0}return u},e("flow",new o)});