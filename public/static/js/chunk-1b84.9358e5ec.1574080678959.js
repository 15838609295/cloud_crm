(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-1b84"],{D1To:function(e,t,a){"use strict";var s=a("GIzL");a.n(s).a},Djp4:function(e,t,a){"use strict";a.r(t);var s=a("LFaP"),n=a("zAZ/"),r={components:{PieChart:s.a},data:function(){return{searchTerms:{type:1,examId:""},examList:[],chartData:[],tableMaxHeight:document.documentElement.clientHeight-380,tableLoading:!1,currentPage:1,pageSize:5,pageSizes:[5,10,50,100],total:0}},watch:{},created:function(){this.getExamList()},methods:{handleCurrentChange:n.c,handleSizeChange:n.d,handleSortChange:n.e,parseTime:n.f,getData:function(){var e=this;if(this.searchTerms.examId){this.tableLoading=!0;var t={pageNo:this.currentPage,pageSize:this.pageSize,id:this.searchTerms.examId,type:this.searchTerms.type};this.$store.dispatch("GetConnect",{url:"exam/examAnalyse",data:t}).then(function(t){e.total=t.data.total,e.chartData=t.data.rows,e.tableLoading=!1}).catch(function(t){e.tableLoading=!1,e.$message.error(t.msg+",请刷新或联系管理员")})}},getExamList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"exam/itemListNoPage"}).then(function(t){e.examList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},toAddQuestion:function(){this.form.question.reverse(),this.form.question.push({title:"",type:0,questionType:0,optionsType:0,options:[{value:""}]}),this.form.question.reverse()},toAddQuestionOptions:function(e){this.form.question[e].options.push({value:""})},toDeleteQuestionOptions:function(e,t){this.form.question[e].options.splice(t,1)},toDeleteQuestion:function(e){this.form.question.splice(e,1)},getUsersList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"user/all-list"}).then(function(t){e.usersList=t.data}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},toExport:function(){this.downloadLoading=!0,window.open(this.$store.getters.serverUrl+"/admin/exam/examAnalyseToExcel?token="+this.$store.getters.token+"&id="+this.searchTerms.examId),this.downloadLoading=!1}}},i=(a("D1To"),a("KHd+")),o=Object(i.a)(r,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container bg-gray"},[a("el-card",{staticClass:"box-1"},[a("div",{attrs:{slot:"header"},slot:"header"},[a("span",[e._v("试题分析")]),e._v(" "),e.searchTerms.examId?a("el-button",{staticClass:"bg-green",staticStyle:{float:"right"},attrs:{type:"success",size:"small"},on:{click:e.toExport}},[e._v("导出数据")]):e._e()],1),e._v(" "),a("div",{staticClass:"header"},[a("el-select",{staticClass:"select",attrs:{placeholder:"请选择考试",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.examId,callback:function(t){e.$set(e.searchTerms,"examId",t)},expression:"searchTerms.examId"}},e._l(e.examList,function(e){return a("el-option",{key:e.id,attrs:{value:e.id,label:e.name}})})),e._v(" "),a("el-select",{staticClass:"select",attrs:{placeholder:"显示类型",clearable:""},on:{change:e.getData},model:{value:e.searchTerms.type,callback:function(t){e.$set(e.searchTerms,"type",t)},expression:"searchTerms.type"}},[a("el-option",{attrs:{value:1,label:"列表"}}),e._v(" "),a("el-option",{attrs:{value:2,label:"饼状图"}})],1)],1),e._v(" "),e._l(e.chartData,function(t,s){return a("div",{key:t.id,staticClass:"content margin-top-20"},[a("div",{staticClass:"title"},[e._v(e._s(s+1)+". "+e._s(t.name)+"（"+e._s(1===t.type?"单选":2===t.type?"多选":"填空题")+"）")]),e._v(" "),a("div",{staticClass:"value"},[1===e.searchTerms.type||3===t.type?a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.tableLoading,expression:"tableLoading"}],attrs:{data:t.option,"max-height":e.tableMaxHeight},on:{"sort-change":e.handleSortChange}},[a("el-table-column",{attrs:{label:"选项",width:"100"},scopedSlots:e._u([{key:"default",fn:function(a){return[e._v("\n              "+e._s(3===t.type?a.$index+1:a.row.label)+"\n            ")]}}])}),e._v(" "),a("el-table-column",{attrs:{prop:"value",label:"内容","min-width":"160"}}),e._v(" "),a("el-table-column",{attrs:{prop:"number",label:"小计",width:"140","header-align":"center",align:"center"}}),e._v(" "),3!==t.type?a("el-table-column",{attrs:{prop:"choice",label:"百分比",width:"140","header-align":"center",align:"center"}}):e._e()],1):e._e(),e._v(" "),2===e.searchTerms.type&&3!==t.type?a("pie-chart",{staticClass:"margin-top-20",attrs:{"chart-data":t.chartData}}):e._e()],1)])}),e._v(" "),a("el-pagination",{staticClass:"pagination",attrs:{"current-page":e.currentPage,"page-size":e.pageSize,"page-sizes":e.pageSizes,total:e.total,layout:"total, sizes, prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.currentPage=t},"current-change":e.handleCurrentChange,"size-change":e.handleSizeChange}})],2)],1)},[],!1,null,"f4ea5806",null);o.options.__file="analysis.vue";t.default=o.exports},GIzL:function(e,t,a){},LFaP:function(e,t,a){"use strict";var s=a("MT78"),n=a.n(s),r=a("7Qib");a("gX0l");var i={props:{className:{type:String,default:"chart"},width:{type:String,default:"100%"},height:{type:String,default:"300px"},chartData:{type:Object,required:!0,default:function(){return{title:"实例",data:[{value:335,name:"已付款"},{value:310,name:"待付款"},{value:234,name:"作废"}]}}}},data:function(){return{chart:null}},watch:{chartData:{deep:!0,handler:function(e){this.setOptions(e)}}},mounted:function(){var e=this;this.initChart(),this.__resizeHandler=Object(r.a)(function(){e.chart&&e.chart.resize()},100),window.addEventListener("resize",this.__resizeHandler)},beforeDestroy:function(){this.chart&&(window.removeEventListener("resize",this.__resizeHandler),this.chart.dispose(),this.chart=null)},methods:{setOptions:function(e){this.chart.setOption({title:{subtext:"",x:"center"},tooltip:{trigger:"item",formatter:"{a} <br/>{b} : {c} ({d}%)"},legend:{orient:"vertical",left:"left",data:e.data},series:[{name:e.title,type:"pie",radius:"55%",center:["50%","60%"],data:e.data,itemStyle:{emphasis:{shadowBlur:10,shadowOffsetX:0,shadowColor:"rgba(0, 0, 0, 0.5)"}}}]})},initChart:function(){this.chart=n.a.init(this.$el,"macarons"),this.setOptions(this.chartData)}}},o=a("KHd+"),c=Object(o.a)(i,function(){var e=this.$createElement;return(this._self._c||e)("div",{class:this.className,style:{height:this.height,width:this.width}})},[],!1,null,null,null);c.options.__file="PieChart.vue";t.a=c.exports},"zAZ/":function(e,t,a){"use strict";a.d(t,"e",function(){return r}),a.d(t,"c",function(){return i}),a.d(t,"d",function(){return o}),a.d(t,"a",function(){return c}),a.d(t,"f",function(){return l}),a.d(t,"b",function(){return h});var s=a("EJiy"),n=a.n(s);function r(e){var t=e.column,a=e.prop,s=e.order;this.order={column:t,prop:a,order:s},this.getData({sortName:a,sortOrder:s&&("ascending"===s?"asc":"desc")})}function i(e){this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function o(e){this.$store.getters.userInfo.pageSize=e,this.pageSize=e,this.getData(this.order?{sortName:this.order.prop,sortOrder:this.order.order&&("ascending"===this.order.order?"asc":"desc")}:{})}function c(e){this.th[e].check=!this.th[e].check}function l(e,t){if(0===arguments.length)return null;var a=t||"{y}-{m}-{d} {h}:{i}:{s}",s=void 0;"object"===(void 0===e?"undefined":n()(e))?s=e:(10===(""+e).length&&(e=1e3*parseInt(e)),s=new Date(e));var r={y:s.getFullYear(),m:s.getMonth()+1,d:s.getDate(),h:s.getHours(),i:s.getMinutes(),s:s.getSeconds(),a:s.getDay()};return a.replace(/{(y|m|d|h|i|s|a)+}/g,function(e,t){var a=r[t];return"a"===t?["日","一","二","三","四","五","六"][a]:(e.length>0&&a<10&&(a="0"+a),a||0)})}function h(e){var t=e.substr(0,10);t=t.replace(/-/g,"/");var a=new Date(t),s=new Date;s=s.valueOf();var n=Math.ceil((a-s)/864e5);return 0===n?"今天":n>0&&n<30?n+"天":n>=30&&n<360?Math.ceil(n/30)+"月":n>=360?Math.ceil(n/360)+"年":"逾期"+Math.ceil((s-a)/864e5)+"天"}}}]);