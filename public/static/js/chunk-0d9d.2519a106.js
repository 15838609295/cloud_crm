(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-0d9d"],{"+y5i":function(e,t,a){},"2Cbl":function(e,t,a){"use strict";var r=a("+y5i");a.n(r).a},"7Qib":function(e,t,a){"use strict";a.d(t,"d",function(){return c}),a.d(t,"a",function(){return u}),a.d(t,"c",function(){return h});var r=a("14Xm"),n=a.n(r),s=a("D3Ub"),i=a.n(s),o=(a("4d7F"),a("EJiy")),l=a.n(o);function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":l()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var n={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=n[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e,t,a){var r=void 0,n=void 0,s=void 0,i=void 0,o=void 0,l=function l(){var c=+new Date-i;c<t&&c>0?r=setTimeout(l,t-c):(r=null,a||(o=e.apply(s,n),r||(s=n=null)))};return function(){for(var n=arguments.length,c=Array(n),u=0;u<n;u++)c[u]=arguments[u];s=this,i=+new Date;var h=a&&!r;return r||(r=setTimeout(l,t)),h&&(o=e.apply(s,c),s=c=null),o}}function h(e){var t=void 0,a=e.split("-"),r=parseInt(a[0]),n=parseInt(a[1]),s=parseInt(a[2]),i=new Date,o=i.getFullYear(),l=i.getMonth()+1,c=i.getDate();if(o===r)t=0;else{var u=o-r;if(u>0)if(l===n)t=c-s<0?u-1:u;else t=l-n<0?u-1:u;else t=-1}return t}t.b=function(){var e=i()(n.a.mark(function e(t,a,r){var s;return n.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return console.log(t),(s=new Image).src=t,e.next=5,s.onload;case 5:if(console.log(s.width,s.height),a/r!=s.width/s.height){e.next=8;break}return e.abrupt("return",s.width+"px * "+s.height+"px");case 8:return e.abrupt("return",!1);case 9:case"end":return e.stop()}},e,this)}));return function(t,a,r){return e.apply(this,arguments)}}()},YqdL:function(e,t,a){"use strict";a.r(t);var r=a("MT78"),n=a.n(r),s=a("7Qib");a("gX0l");var i={props:{className:{type:String,default:"chart"},width:{type:String,default:"100%"},height:{type:String,default:"300px"},chartData:{type:Object,required:!0,default:function(){return{title:"实例",data:[{value:335,name:"已付款"},{value:310,name:"待付款"},{value:234,name:"作废"}]}}}},data:function(){return{chart:null}},watch:{chartData:{deep:!0,handler:function(e){this.setOptions(e)}}},mounted:function(){var e=this;this.initChart(),this.__resizeHandler=Object(s.a)(function(){e.chart&&e.chart.resize()},100),window.addEventListener("resize",this.__resizeHandler)},beforeDestroy:function(){this.chart&&(window.removeEventListener("resize",this.__resizeHandler),this.chart.dispose(),this.chart=null)},methods:{setOptions:function(e){this.chart.setOption({title:{text:e.title,subtext:"",x:"center"},tooltip:{trigger:"item",formatter:"{a} <br/>{b} : {c} ({d}%)"},legend:{orient:"vertical",left:"left",data:e.data},series:[{name:e.title,type:"pie",radius:"55%",center:["50%","60%"],data:e.data,itemStyle:{emphasis:{shadowBlur:10,shadowOffsetX:0,shadowColor:"rgba(0, 0, 0, 0.5)"}}}]})},initChart:function(){this.chart=n.a.init(this.$el,"macarons"),this.setOptions(this.chartData)}}},o=a("KHd+"),l=Object(o.a)(i,function(){var e=this.$createElement;return(this._self._c||e)("div",{class:this.className,style:{height:this.height,width:this.width}})},[],!1,null,null,null);l.options.__file="PieChart.vue";var c=l.exports,u=a("zAZ/"),h={components:{PieChart:c},data:function(){return{searchTerms:{type:0,state:0,showType:0},chartData:[{title:"這是題目這是題目這是題目這是題目",data:[{value:335,name:"选项一",label:"选项一选项一选项一"},{value:310,name:"选项二",label:"选项一选项一选项一"},{value:234,name:"选项三",label:"选项一选项一选项一"}]},{title:"這是題目這是題目這是題目這是題目",data:[{value:335,name:"选项一",label:"选项一选项一选项一"},{value:310,name:"选项二",label:"选项一选项一选项一"},{value:234,name:"选项三",label:"选项一选项一选项一"}]}],form:{way:[]},tableMaxHeight:document.documentElement.clientHeight-380,tableLoading:!1,currentPage:1,pageSize:5,pageSizes:[5,10,50,100],total:0}},watch:{},created:function(){this.id=this.$route.query.id,this.getData()},methods:{handleCurrentChange:u.c,handleSizeChange:u.d,handleSortChange:u.e,parseTime:u.f,getData:function(e){var t=this;this.tableLoading=!0,e||(e={});var a={page_no:this.currentPage,page_size:4,search:this.searchTerms.searchValue,status:this.searchTerms.status,type:this.searchTerms.type};this.searchTerms.date&&0!==this.searchTerms.date.length&&(a.start_time=this.searchTerms.date[0]+" 00:00:00",a.end_time=this.searchTerms.date[1]+" 23:59:59"),e.sortName&&(a.sortName=e.sortName),e.sortOrder&&(a.sortOrder=e.sortOrder),this.$store.dispatch("GetConnect",{url:"achievement/list",data:a}).then(function(e){t.tableData=e.data.rows,t.tableLoading=!1}).catch(function(e){t.tableLoading=!1,t.$message.error(e.msg+",请刷新或联系管理员")})},toAddQuestion:function(){this.form.question.reverse(),this.form.question.push({title:"",type:0,questionType:0,optionsType:0,options:[{value:""}]}),this.form.question.reverse()},toAddQuestionOptions:function(e){this.form.question[e].options.push({value:""})},toDeleteQuestionOptions:function(e,t){this.form.question[e].options.splice(t,1)},toDeleteQuestion:function(e){this.form.question.splice(e,1)},getUsersList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/all-list"}).then(function(t){e.usersList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var a=t.form;a.sale_proof=a.pic_list;var r="achievement/edit/"+t.id;t.$store.dispatch("GetConnect",{url:r,data:a}).then(function(e){t.$message.success(e.msg),t.$router.back(-1)}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetForm:function(e){this.$refs[e].resetFields()}}},d=(a("2Cbl"),Object(o.a)(h,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{attrs:{slot:"header"},slot:"header"},[a("span",[e._v("统计数据")]),e._v(" "),a("el-button",{staticClass:"bg-green",staticStyle:{float:"right"},attrs:{type:"success",size:"small"}},[e._v("导出数据")])],1),e._v(" "),a("div",{staticClass:"header"},[a("el-select",{staticClass:"select",attrs:{placeholder:"选择问卷",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.status,callback:function(t){e.$set(e.searchTerms,"status",t)},expression:"searchTerms.status"}},[a("el-option",{attrs:{value:1,label:"正在进行"}}),e._v(" "),a("el-option",{attrs:{value:0,label:"有结束"}}),e._v(" "),a("el-option",{attrs:{value:-1,label:"已过期"}})],1),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"选择题号",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.status,callback:function(t){e.$set(e.searchTerms,"status",t)},expression:"searchTerms.status"}},[a("el-option",{attrs:{value:1,label:"正在进行"}}),e._v(" "),a("el-option",{attrs:{value:0,label:"有结束"}}),e._v(" "),a("el-option",{attrs:{value:-1,label:"已过期"}})],1)],1),e._v(" "),e._l(e.chartData,function(t,r){return a("div",{key:t,staticClass:"content margin-top-20"},[a("div",{staticClass:"title"},[e._v(e._s(r+1)+". 這是題目這是題目這是題目這是題目?")]),e._v(" "),a("div",{staticClass:"value"},[a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:t.data,"max-height":e.tableMaxHeight},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{prop:"name",label:"选项",width:"100"}}),e._v(" "),a("el-table-column",{attrs:{prop:"label",label:"内容","min-width":"160"}}),e._v(" "),a("el-table-column",{attrs:{prop:"name",label:"小计",width:"140","header-align":"center",align:"center"}}),e._v(" "),a("el-table-column",{attrs:{prop:"buy_time",label:"百分比",width:"200","header-align":"center",align:"center"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n              "+e._s(e.parseTime(t.row.buy_time,"{y}-{m}-{d}"))+"\n            ")]}}])})],1),e._v(" "),a("pie-chart",{staticClass:"margin-top-20",attrs:{"chart-data":t}})],1)])}),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],2)],1)},[],!1,null,"1c7dd788",null));d.options.__file="statistics.vue";t.default=d.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return s}),a.d(t,"c",function(){return i}),a.d(t,"d",function(){return o}),a.d(t,"a",function(){return l}),a.d(t,"f",function(){return c}),a.d(t,"b",function(){return u});var r=a("EJiy"),n=a.n(r);function s(e){var t=e.column,a=e.prop,r=e.order;this.order={column:t,prop:a,order:r},this.getData({sortName:a,sortOrder:r&&("ascending"===r?"asc":"desc")})}function i(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function l(e){this.th[e].check=!this.th[e].check}function c(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",r=void 0;"object"===(void 0===e?"undefined":n()(e))?r=e:(10===(""+e).length&&(e=1e3*parseInt(e)),r=new Date(e));var s={y:r.getFullYear(),m:r.getMonth()+1,d:r.getDate(),h:r.getHours(),i:r.getMinutes(),s:r.getSeconds(),a:r.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=s[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function u(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),r=new Date;r=r.valueOf();var n=Math.ceil((a-r)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((r-a)/864e5)+"天"}}}]);