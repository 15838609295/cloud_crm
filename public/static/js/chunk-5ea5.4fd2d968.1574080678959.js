(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-5ea5"],{"61fh":function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAADjElEQVR4Xu2a7dENQRCFzxsBGSACRIAIEAEiQASIABEgAkSACBABGSAC6lE7pfXdnY/dnbvz3p2puj9u7W5Pn9NfMz1zpp2Ps53jVyege8DOGeghsHMHqJIEPzhSby0keW15/6lTIwR+O8BL51hbXifAMrDUOmPevbbF1pY32wMuSrot6bKka5L4H8ZXSY+GPymFfUx7En3OmJJ3X9I98/FPSV+G3ydJ/E+OHA8A6ENJTyPSmPBmJgEekBfrdZoiAH2eTOgE+BeSnqUYSBEAeCyGxWOjNQKCrh8l3Y15Q4qAzxngmaxVAtDttaQHU9aLETDmYr8G1yLWbIyF+GOeVA4IoTKlE1azY0oeuYhfGCE3kRsuOBl4wbuxCWME/HCJDivfyUguKQJSYemfl8qDCMBeNYIw2PUSArCSzdZYHsE5mbVU4RQhc+SRswhfO65I+p7KuOE5Je25efn9YP2UsjkhkCMjJwRScrC69QLKqw+vyb2Aj3/KSawMWmV8jB9MmtLcPZ8rj3lvGFlHI6AQX7XXOwHdA3oI9BzQk2CvAv8Y6GXwWAuhaoW9UHBfB5ziOoCtLt0p4jm1MTs5DwD8qyEM2O6yx4+NkyIA4BBgx0vTiB0j4mQIGANPf4JdIlveqXESBMwFDylNEIDbhl59YRX7G+/e7XMsH+bZnADaUqGt9njozuaSsBT85h7AeQI9Odu1jbanB2b4DvA0X+0osXwTHuB7ikEpwmGqhk8dwnDsRsJL1X3vXZuHAPHL8ZTv0QMEEmwGXxv85iEQrEEeYNFyaST4Oa0hLGqAb4YAFAEgJNjmROADAiDJnz3OdXvL8+Yh4I0eO821764BvikPsODI7ljd54XwDgcw5I7ShHeulsK4OyTYUxsAvBlZ+OSuG84VASEvQAI3TmqAbzYExvICiyW/5F1i+SYWQmsAWCqjuSqwFFDp952AJT3BJfcDSi1V631/v6noXMDfEKEuc8NijfpcC7CVS2L95iYquiHCt4C1C5acRuQxwKXmYOn91txb5H1Wl6NX/UpvibGDo37HenEpBWs+x3MpqbYPwXxh43Uwd4wAmCST+hVbTQA1ZNs7jEUE8HJsO1tD2bVlJjdWqZuiYdlKY8NeTF5b0RryODdgBxpN3DkEBOWIK3ZzxJm9KV5D+bkyAUvYkrAP7gSOCS0hYK5STX/XCWjaPEdQrnvAEUhueorde8Af9GAfUBpP84AAAAAASUVORK5CYII="},Nuyh:function(t,e,i){"use strict";var n=i("b8Th");i.n(n).a},Zl2N:function(t,e,i){"use strict";i.r(e);var n=[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"title"},[e("img",{attrs:{src:i("61fh"),alt:""}}),this._v(" "),e("div",[this._v("已签到")])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"title"},[e("img",{attrs:{src:i("61fh"),alt:""}}),this._v(" "),e("div",[this._v("未签到")])])}],s=i("yZE4"),a=i.n(s),r={data:function(){return{imageUrl:"",defaultAvatar:a.a,isShow:!1,signedList:[],unsignedList:[]}},watch:{$route:function(t){var e=this;"打卡签到"===t.name?(this.getData(),setTimeout(function(){e.isShow=!0},500)):this.isShow=!1}},created:function(){"打卡签到"===this.$route.name&&(this.isShow=!0,this.getData())},methods:{parseTime:i("zAZ/").f,getData:function(){var t=this;this.$store.dispatch("GetConnect",{url:"statistic/work-list"}).then(function(e){t.signedList=e.data.on_work,t.unsignedList=e.data.out_of_work}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},memberClick:function(t){this.isShow=!1,this.$router.push({path:"/workbench/sign_in/personalty",query:{id:t}})}}},c=(i("Nuyh"),i("KHd+")),l=Object(c.a)(r,function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"app-container bg-gray"},[i("transition",{attrs:{name:"fade-transform",mode:"out-in"}},[i("router-view")],1),t._v(" "),t.isShow?i("div",{staticClass:"signed"},[t._m(0),t._v(" "),i("ul",t._l(t.signedList,function(e,n){return i("li",{key:n,staticClass:"card",on:{click:function(i){t.memberClick(e.id)}}},[i("img",{attrs:{src:e.wechat_pic?e.wechat_pic.indexOf("http")>=0?e.wechat_pic:t.imageUrl+e.wechat_pic:t.defaultAvatar,alt:""}}),t._v(" "),i("h1",[t._v(t._s(e.name)),i("span",[t._v(" ("+t._s(e.company_name)+")")])]),t._v(" "),i("div"),t._v(" "),i("p",[t._v("打卡时间："+t._s(t.parseTime(e.work_time,"{h}:{i}:{s}")))]),t._v(" "),i("p",[t._v("今日派单："+t._s(e.customer_number))]),t._v(" "),i("p",[t._v("今日成交："+t._s(e.deal_number))]),t._v(" "),i("span",{staticClass:"card-caption open-down"},[i("p",[t._v(" ")]),t._v(" "),i("p",[t._v(" ")]),t._v(" "),i("p",[t._v("入职时间:  "+t._s(t.parseTime(e.hiredate,"{y}-{m}-{d}")))]),t._v(" "),i("p",[t._v("工作状态:  "+t._s(1===e.work_status?"接待":"不接待"))]),t._v(" "),i("p",[t._v("本月客户数:  "+t._s(e.month_customer_number))]),t._v(" "),i("p",[t._v("本月成交数:  "+t._s(e.month_deal_number))])])])}))]):t._e(),t._v(" "),t.isShow?i("div",{staticClass:"unsigned"},[t._m(1),t._v(" "),i("ul",t._l(t.unsignedList,function(e,n){return i("li",{key:n,on:{click:function(i){t.memberClick(e.id)}}},[i("img",{attrs:{src:e.wechat_pic?e.wechat_pic.indexOf("http")>=0?e.wechat_pic:t.imageUrl+e.wechat_pic:t.defaultAvatar,alt:""}}),t._v(" "),i("h1",[t._v(t._s(e.name)),i("span",[t._v(" ("+t._s(e.company_name)+")")])])])}))]):t._e()],1)},n,!1,null,"dea2b308",null);l.options.__file="sign_in.vue";e.default=l.exports},b8Th:function(t,e,i){},yZE4:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHMAAABzCAYAAACrQz3mAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkJGMTQ4NURGNTM0QjExRThCNzdGQUMzQ0Q5MzlCMDU5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkJGMTQ4NUUwNTM0QjExRThCNzdGQUMzQ0Q5MzlCMDU5Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QkYxNDg1REQ1MzRCMTFFOEI3N0ZBQzNDRDkzOUIwNTkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QkYxNDg1REU1MzRCMTFFOEI3N0ZBQzNDRDkzOUIwNTkiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7BFlFxAAAPT0lEQVR42uyd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"},"zAZ/":function(t,e,i){"use strict";i.d(e,"e",function(){return a}),i.d(e,"c",function(){return r}),i.d(e,"d",function(){return c}),i.d(e,"a",function(){return l}),i.d(e,"f",function(){return m}),i.d(e,"b",function(){return h});var n=i("EJiy"),s=i.n(n);function a(t){var e=t.column,i=t.prop,n=t.order;this.order={column:e,prop:i,order:n},this.getData({sortName:i,sortOrder:n&&("ascending"===n?"asc":"desc")})}function r(t){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(t){this.$store.getters.userInfo.pageSize=t,this.pageSize=t,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(t){this.th[t].check=!this.th[t].check}function m(t,e){if(0===arguments.length)return null;var i=e||"{y}-{m}-{d} {h}:{i}:{s}",n=void 0;"object"===(void 0===t?"undefined":s()(t))?n=t:(10===(""+t).length&&(t=1e3*parseInt(t)),n=new Date(t));var a={y:n.getFullYear(),m:n.getMonth()+1,d:n.getDate(),h:n.getHours(),i:n.getMinutes(),s:n.getSeconds(),a:n.getDay()};return i.replace(/{(y|m|d|h|i|s|a)+}/g,function(t,e){var i=a[e];return"a"===e?["日","一","二","三","四","五","六"][i]:(t.length>0&&i<10&&(i="0"+i),i||0)})}function h(t){var e=t.substr(0,10);e=e.replace(/-/g,"/");var i=new Date(e),n=new Date;n=n.valueOf();var s=Math.ceil((i-n)/864e5);return 0===s?"今天":s>0&&s<30?s+"天":s>=30&&s<360?Math.ceil(s/30)+"月":s>=360?Math.ceil(s/360)+"年":"逾期"+Math.ceil((n-i)/864e5)+"天"}}}]);