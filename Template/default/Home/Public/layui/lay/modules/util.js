"use strict";var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(o){return typeof o}:function(o){return o&&"function"==typeof Symbol&&o.constructor===Symbol&&o!==Symbol.prototype?"symbol":typeof o};layui.define("jquery",function(o){var l=layui.jquery,t={fixbar:function(o){o=o||{},o.bgcolor=o.bgcolor?"background-color:"+o.bgcolor:"";var t,i,c="layui-fixbar-top",r=[o.bar1===!0?"&#xe606;":o.bar1,o.bar2===!0?"&#xe607;":o.bar2,"&#xe604;"],a=l(['<ul class="layui-fixbar">',o.bar1?'<li class="layui-icon" lay-type="bar1" style="'+o.bgcolor+'">'+r[0]+"</li>":"",o.bar2?'<li class="layui-icon" lay-type="bar2" style="'+o.bgcolor+'">'+r[1]+"</li>":"",'<li class="layui-icon '+c+'" lay-type="top" style="'+o.bgcolor+'">'+r[2]+"</li>","</ul>"].join("")),y=a.find("."+c),e=function(){l(document).scrollTop()>=(o.showHeight||200)?i||(y.show(),i=1):i&&(y.hide(),i=0)};l(".layui-fixbar")[0]||("object"==_typeof(o.css)&&a.css(o.css),l("body").append(a),e(),a.find("li").on("click",function(){var t=l(this),i=t.attr("lay-type");"top"===i&&l("html,body").animate({scrollTop:0},200),o.click&&o.click.call(this,i)}),l(document).on("scroll",function(){t&&clearTimeout(t),t=setTimeout(function(){e()},100)}))}};o("util",t)});