(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-d6ff"],{"+V0B":function(e,t,s){},HksL:function(e,t,s){"use strict";s.r(t);var a=s("14Xm"),r=s.n(a),o=s("D3Ub"),n=s.n(o),i=s("Mx9/"),c=s("zAZ/"),d=s("YV90"),l=s.n(d),u={data:function(){return{zipImage:l.a,hasUpdate:!1,newVersion:{url:"",version_number:"",remarks:""},updateDialogVisible:!1,updateProgressVisible:!1,updateProgress:0,updateProgressValue:"",updateProgressStatus:"检查更新文件",siteInfo:"",info:""}},watch:{},created:function(){this.getData(),this.getSiteData()},methods:{parseTime:c.f,getData:function(e){var t=this;e||(e={});this.$store.dispatch("GetConnect",{url:"index/index"}).then(function(e){t.info=e.data}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},getSiteData:function(){var e=this;this.$store.dispatch("GetConnect",{url:"configs/index"}).then(function(t){e.siteInfo=t.data,1===e.$store.getters.id&&"LOCAL"===i.a.ENV&&e.checkUpdate()}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},logsClick:function(){this.isShow=!1,this.$router.push({path:"/workbench/index/logs"})},getHasUpdate:function(){"LOCAL"===i.a.ENV&&(this.$store.dispatch("SetIsUpdate",!0),this.checkUpdate())},checkUpdate:function(){var e=this;this.$store.dispatch("GetConnect",{url:"check"}).then(function(t){1===t.status&&(e.hasUpdate=!0,e.$store.getters.isUpdate&&e.$confirm("CRM系统有新发布版本，是否查看更新说明并更新?","更新",{confirmButtonText:"查看更新",cancelButtonText:"以后更新",type:"warning"}).then(function(){e.updateDialogVisible=!0,e.newVersion=t.data}).catch(function(){e.$store.dispatch("SetIsUpdate",!1),e.$alert("可通过系统设置--\x3e站点信息中进行更新","提示",{confirmButtonText:"确定"})}))}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},stopWebSite:function(){var e=this;this.$store.dispatch("GetConnect",{url:"settingOutdated",data:{}}).then(function(e){console.log("=========关闭站点========="),console.log(e)}).catch(function(t){e.$message.error(t.msg)})},doUpdate:function(){var e=this;return n()(r.a.mark(function t(){var s,a,o,n,i,c,d,l,u,g,p,h,f,x,m,b,v,w,k;return r.a.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:e.queryMatch(204)&&e.stopWebSite(),e.updateProgressVisible=!0,s=0,a="readyDownload",o=null,e.updateProgressStatus="检查更新包...",n=1;case 7:if(!(n<=e.newVersion.number)){t.next=33;break}return i={url:e.newVersion.url,number:n},e.updateProgressValue="检查更新包--\x3e"+n,e.updateProgress=parseFloat((e.updateProgress+15/e.newVersion.number).toFixed(2)),console.log("检查更新包--\x3e"+n),t.next=14,e.$store.dispatch("GetConnect",{url:a,data:i});case 14:return o=t.sent,t.t0=console,t.next=18,o;case 18:if(t.t1=t.sent,t.t0.log.call(t.t0,t.t1),!o.data||1!==o.data.result){t.next=30;break}if(3!==++s){t.next=28;break}return e.$message.error("更新失败,更新包异常,请联系管理员"),e.updateProgressStatus="更新失败...",e.updateProgressValue=o.msg,e.updateProgress=100,t.abrupt("return");case 28:n=0,e.updateProgress=0;case 30:n++,t.next=7;break;case 33:console.log("=========更新包文件名列表========="),console.log(o),a="readyUnzip",e.updateProgressStatus="解压更新文件...",c=0;case 38:if(!(c<o.data.length)){t.next=48;break}return d={file_name:o.data[c]},e.updateProgressValue=o.data[c],e.updateProgress=parseFloat((e.updateProgress+50/o.data.length).toFixed(2)),console.log("=========解压文件--\x3e"+c),t.next=45,e.$store.dispatch("GetConnect",{url:a,data:d});case 45:c++,t.next=38;break;case 48:return a="getDirName",t.next=51,e.$store.dispatch("GetConnect",{url:a});case 51:l=t.sent,console.log("=========更新文件夹目录列表========="),console.log(l),u=null,a="moveDir",e.updateProgressStatus="覆盖更新文件...",g=0;case 58:if(!(g<l.data.length)){t.next=69;break}return p={dirName:l.data[g]},t.next=62,e.$store.dispatch("GetConnect",{url:a,data:p});case 62:u=t.sent,console.log("=========覆盖更新文件夹--\x3e"+g),e.updateProgressValue=l.data[g],e.updateProgress=parseFloat((e.updateProgress+10/l.data.length).toFixed(2));case 66:g++,t.next=58;break;case 69:return console.log("=========覆盖更新完成========="),console.log(u),a="getDbTableNames",t.next=74,e.$store.dispatch("GetConnect",{url:a});case 74:h=t.sent,console.log("=========获取数据库目录列表========="),console.log(h),a="backupsTableName",f=(new Date).getTime(),e.updateProgressStatus="备份数据库数据...",x=0;case 81:if(!(x<h.data.length)){t.next=100;break}m=0;case 83:if(!(m<h.data[x].number)){t.next=97;break}return b={table:h.data[x].table,number:m+1},v=(new Date).getTime(),t.next=88,e.$store.dispatch("GetConnect",{url:a,data:b});case 88:u=t.sent,w=(new Date).getTime(),console.log("=========备份数据库数据"+(x+1)+"--\x3e"+h.data[x].table+"--\x3e"+(m+1)+"--\x3e耗时: "+(w-v)),e.updateProgressStatus="备份数据库数据("+x+"/"+h.data.length+")...",e.updateProgressValue=h.data[x].table+"--\x3e"+(m+1),e.updateProgress=parseFloat((e.updateProgress+20/h.data.length/h.data[x].number).toFixed(2));case 94:m++,t.next=83;break;case 97:x++,t.next=81;break;case 100:return console.log("=========备份数据库数据完成========="),e.updateProgress=parseFloat((e.updateProgress+20).toFixed(2)),k=(new Date).getTime(),console.log("总耗时: "+(k-f)/1e3),console.log(u),a="updateDatabase",e.updateProgressStatus="更新数据库文件...",t.next=109,e.$store.dispatch("GetConnect",{url:a});case 109:u=t.sent,e.updateProgressValue="更新中...",e.updateProgress=parseFloat((e.updateProgress+5).toFixed(2)),console.log("=========更新数据库文件完成========="),console.log(u),0===u.code?(e.updateProgressStatus="已完成...",e.updateProgressValue="",e.updateProgress=100):(e.updateProgressStatus="更新失败...",e.updateProgressValue=u.msg,e.updateProgress=100);case 115:case"end":return t.stop()}},t,e)}))()},refresh:function(){this.$router.go(0)}}},g=(s("Lfrk"),s("KHd+")),p=Object(g.a)(u,function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"app-container  bg-gray"},[s("el-row",{attrs:{gutter:20}},[s("el-col",{attrs:{xs:24,sm:24,md:24,lg:14,xl:16}},[s("el-card",{staticClass:"box-1"},[s("div",{staticClass:"flex-row",attrs:{slot:"header"},slot:"header"},[s("span",[e._v("最新公告")]),e._v(" "),s("div",{staticClass:"text-right cursor-p",on:{click:e.logsClick}},[e._v("查看更多")])]),e._v(" "),s("el-table",{staticStyle:{width:"100%"},attrs:{data:e.info.articles,"show-header":!1,"row-class-name":"cursor-p"},on:{"cell-click":e.logsClick}},[s("el-table-column",{attrs:{prop:"created_at",width:"120"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(e.parseTime(t.row.created_at,"{y}-{m}-{d}"))+"\n            ")]}}])}),e._v(" "),s("el-table-column",{attrs:{prop:"title","min-width":"260"}})],1)],1)],1),e._v(" "),s("el-col",{staticStyle:{width:"240px"}},[s("el-card",{staticClass:"wxapp-img-box"},[s("div",{attrs:{slot:"header"},slot:"header"},[s("span",[e._v("小程序二维码")])]),e._v(" "),s("div",{staticClass:"wxapp-item"},[s("ws-show-img",{staticStyle:{width:"152px",height:"152px"},attrs:{src:e.info.member_wechat_qr}})],1)])],1)],1),e._v(" "),s("el-card",{staticClass:"site-box"},[s("el-row",[s("div",{staticClass:"header"},[e._v("系统信息")])]),e._v(" "),e._l(e.siteInfo,function(t,a){return s("el-row",{key:a},[s("el-col",{attrs:{sm:8,md:7,lg:6,xl:4}},[e._v(e._s(t.con_name))]),e._v(" "),s("el-col",{attrs:{sm:16,md:17,lg:18,xl:20}},[e._v(e._s(t.con_value)+"\n        "),1===a&&1===e.$store.getters.id?s("el-button",{attrs:{type:"text"},on:{click:e.getHasUpdate}},[e._v("检查更新")]):e._e(),e._v(" "),1===a&&e.hasUpdate&&1===e.$store.getters.id?s("el-button",{attrs:{type:"text"},on:{click:e.getHasUpdate}},[e._v("（有新版本, 点击更新）\n        ")]):e._e()],1)],1)})],2),e._v(" "),s("el-dialog",{attrs:{visible:e.updateDialogVisible,"modal-append-to-body":!1,title:"系统更新说明",width:"1080px"},on:{"update:visible":function(t){e.updateDialogVisible=t}}},[s("div",{domProps:{innerHTML:e._s(e.newVersion.remarks)}},[e._v(e._s(e.newVersion.remarks||"暂无更新内容说明"))]),e._v(" "),s("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[s("el-button",{on:{click:function(t){e.updateDialogVisible=!1}}},[e._v("以 后 更 新")]),e._v(" "),s("el-button",{attrs:{type:"primary"},on:{click:e.doUpdate}},[e._v("确 定 更 新")])],1)]),e._v(" "),s("el-dialog",{attrs:{visible:e.updateProgressVisible,"show-close":!1,"close-on-press-escape":!1,"close-on-click-modal":!1,"modal-append-to-body":!1,title:"更新中...",width:"520px"},on:{"update:visible":function(t){e.updateProgressVisible=t}}},[s("el-progress",{attrs:{"text-inside":!0,"stroke-width":26,percentage:e.updateProgress}}),e._v(" "),s("div",{staticClass:"progress-status"},[e._v(e._s(e.updateProgressStatus+"..."))]),e._v(" "),s("div",{staticClass:"progress-value"},[e._v(e._s(e.updateProgressValue))]),e._v(" "),s("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[100===e.updateProgress?s("el-button",{attrs:{type:"primary"},on:{click:e.refresh}},[e._v("立即刷新")]):e._e()],1)],1)],1)},[],!1,null,"9507aeda",null);p.options.__file="index.vue";t.default=p.exports},Lfrk:function(e,t,s){"use strict";var a=s("+V0B");s.n(a).a},YV90:function(e,t){e.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAE5UlEQVR4Xu1dXWgcVRg9d3aTbtOAIVhI9cWf1gSlgqG0ZJOAPilSQU1Ln8S3al/UVEi1tCGkUgv+I6hQH6S0vsTYgkWqj5akzUNDRCgtaR8MKFIihpKOabI7t2yqsZ1dc7+dubN7dzkDecl898zdc8535s7uZVeBh1MMKKdmw8mAgjhmAgpCQRxjwLHpsEMoiGMMODYddggFcYwBx6bDDqkHQfrGb3YoFbRV6rXsunGsfZ3218a93tnMU1em0+3zcXEk47X2/hjNrrkkqb2zpqwO2TnhdyFQBwA8W+6F4tS/O/saHsxdjQOxPHa49QguNm6OjVMGwPfw9Dsj25rOSceIBek7f/NxLwjGoNAsBbdVV8OCABrzSKd6R7Y2Tkn4EAuy89zf0wA2SkBt19S0IAUyNC6PZNd2SHgRCdI3vvC0p/QZCWASNTUvCIBAq2dGs5kfTPyIBNkxvvCGUvojE1hS5+tBEK1V/zfZzMcmjmSCnPffVFq9bwJL6nw9CAKtB0ayTe+ZOKIgJoZsnacgxUxWYdn73yQoCAWx1dxFOLyHhCjZwZt6fLMxshhZ8V30PwiMLEaWfXMxshhZ9l31DyIji5Fl31yMLEaWfVcxskpzygdDC15jZDGyLNioNARXWVxl2TcXI4uRZd9VXGWV5nTv6ek9f61r2Z0Y4wbg4et72+8Lfou9c/GTpn1XfmnsrMjOxfBLar0xd/SD7Zs+M3Eo+kx94eWX+hXwoQksqfOpJ69BtSzFhs+fvRf6zzWxcaIAaK0HMseO29nkQEGiSHD3GApSgkN2SARjeT29SPX03D2yZQkqHURAC7l0rgHIecv/1L6P3JdHAd+PjSsBqNkOaXhrP1SHaBushIdVa/Jfn0D+R+PuztjXWTZArd5DKirIqZPInzpphXATCAUxMQQsi0FBDESFO0TPzEA1zgFpLaDYUNK8Ccg0rRRREAGlYUGWjhyG1/aTlecQtflTqIcfpSACHVZKKIiArUo+GFIQCiJgIH5J3ayyeA8pYQZGFjtkhQF2CDskfjuUQOA9pAQpfA6J4DUuewWkVfOmnj9xHGj9Gao5J5jp6iXqodeh7n+AT+rlMMl3ewVsVbNDBNOLXMI3FwXUsUMEJFWyQ1LPv4DCX+KH72Nx8AAwO5v4pQoXqNllbyl2uA0oxEolO4SCCBqWgghIMpRYjazBqe5+6OrtXNz9ewM2LN7evhPn+KptEb9mLHwUHG0SA8NPjNnZuUhBoikQGkVBwjSyQ6wYC2BkhYhkZFlxFiOLkWXFSMUgjCxGVhLWYmQxspLwFbjKKqKVqywrTmNkMbKsGImrLCONjCwjRZICRhYjS+KTCDV8MOSDYQTbGIcwshhZRpNEK2BkMbKiOWf1UYysuo2sg5PdryqFz5OwjQSzHiJLa+w51Dn2hen1ir4va+hC18bA8wq/Y1iVox4E8XTusaHOiYsmAkWCFEAOTnaPKoUXTYBJnK91QbTGt4c6x/ok3IgFeXuyd31aBd8pYJsE2GZNLQuigYlUfmH70JYLok3EYkH+JXhwqusVaPWcRuV+E3fXtYZH7smp2N+5eKY1d3UmE1TkOxcVMK+1Oi25b9xp3rIFsel8YhUzQEEccwUFoSCOMeDYdNghFMQxBhybDjuEgjjGgGPTYYdQEMcYcGw67BDHBLkFfgNzoU8mO4wAAAAASUVORK5CYII="},"zAZ/":function(e,t,s){"use strict";s.d(t,"e",function(){return o}),s.d(t,"c",function(){return n}),s.d(t,"d",function(){return i}),s.d(t,"a",function(){return c}),s.d(t,"f",function(){return d}),s.d(t,"b",function(){return l});var a=s("EJiy"),r=s.n(a);function o(e){var t=e.column,s=e.prop,a=e.order;this.order={column:t,prop:s,order:a},this.getData({sortName:s,sortOrder:a&&("ascending"===a?"asc":"desc")})}function n(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function i(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(e){this.th[e].check=!this.th[e].check}function d(e,t){if(0===arguments.length)return null;var s=t||"{y}-{m}-{d} {h}:{i}:{s}",a=void 0;"object"===(void 0===e?"undefined":r()(e))?a=e:(10===(""+e).length&&(e=1e3*parseInt(e)),a=new Date(e));var o={y:a.getFullYear(),m:a.getMonth()+1,d:a.getDate(),h:a.getHours(),i:a.getMinutes(),s:a.getSeconds(),a:a.getDay()};return s.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var s=o[t];return"a"===t?["日","一","二","三","四","五","六"][s]:(e.length>0&&s<10&&(s="0"+s),s||0)})}function l(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var s=new Date(t),a=new Date;a=a.valueOf();var r=Math.ceil((s-a)/864e5);return 0===r?"今天":r>0&&r<30?r+"天":r>=30&&r<360?Math.ceil(r/30)+"月":r>=360?Math.ceil(r/360)+"年":"逾期"+Math.ceil((a-s)/864e5)+"天"}}}]);