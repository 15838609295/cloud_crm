(window.webpackJsonp=window.webpackJsonp||[]).push([["chunk-a80c"],{JKcB:function(e,t,r){"use strict";var o=r("qONk");r.n(o).a},XnGl:function(e,t,r){"use strict";r.r(t);var o=r("gDS+"),i=r.n(o),l=r("P2sY"),a=r.n(l),s=r("Bhkv"),n=r.n(s),c=r("Mx9/"),d={data:function(){return{ENV:c.a.ENV,title:"",ImageUrl:"",videoUploadUrl:this.$store.getters.serverUrl+"/admin/ajax/uploadVideo",videoUploadProgressVisible:!1,videoUploadProgress:0,typeList:[],form:{read_power:0,is_display:0,articles_type_id:"",picture_type:1,videoType:0},formRules:{title:[{required:!0,message:"请输入新闻标题",trigger:"blur"}],articles_type_id:[{required:!0,message:"选择新闻类型",trigger:"blur"}]}}},created:function(){this.title=this.$route.query.title,this.loadTypeList(),console.log(this.config)},methods:{loadTypeList:function(){var e=this;this.$store.dispatch("GetConnect",{url:"lookarticles/allTypeList"}).then(function(t){e.typeList=t.data,e.$route.query.id?(e.id=e.$route.query.id,e.getNews(e.$route.query.id)):e.form.articles_type_id=e.$route.query.typeId||""}).catch(function(t){e.$message.error(t.msg+",请刷新或联系管理员")})},getNews:function(e){var t=this,r="article/detail/"+e;this.$store.dispatch("GetConnect",{url:r}).then(function(e){(e.data.videoType=0,t.form=a()({},e.data),t.form.articles_type_id=JSON.parse(e.data.articles_type_id),t.form.thumb_url=[],e.data.thumb)&&e.data.thumb.split(",").forEach(function(e){t.form.thumb_url.push({id:e,url:e})})}).catch(function(e){t.$message.error(e.msg+",请刷新或联系管理员")})},submitForm:function(e){var t=this;this.$refs[e].validate(function(e){if(e){var r=a()({},t.form);r.typeid=1,r.articles_type_id=i()(r.articles_type_id);var o=t.id?"article/edit/"+t.id:"article/create";t.$store.dispatch("GetConnect",{url:o,data:r}).then(function(e){t.$message.success(e.msg),t.$router.back(-1)}).catch(function(e){t.$message.error(e.msg)})}else t.$message.error("提交失败,请检查必填项")})},resetForm:function(e){this.$refs[e].resetFields()},getSignature:function(){return this.$store.dispatch("GetConnect",{url:"upload/vodSign"}).then(function(e){return e.data.sign})},upLoadTXVideo:function(e){var t=this;this.videoUploadProgressVisible=!0;var r=new n.a({getSignature:this.getSignature}).upload({mediaFile:e.file});r.on("media_progress",function(e){t.videoUploadProgress=Number(100*e.percent).toFixed(2),console.log(e.percent)}),r.done().then(function(e){t.videoUploadProgressVisible=!1,t.videoUploadProgress=0,t.form.file_url=e.video.url,t.$message.success("视频上传成功~")}).catch(function(e){console.log(e),this.$message.error(e)})}}},u=(r("JKcB"),r("KHd+")),p=Object(u.a)(d,function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("div",[r("el-card",{staticClass:"box-1"},[r("div",{attrs:{slot:"header"},slot:"header"},[r("span",[e._v(e._s(e.title))])]),e._v(" "),r("el-form",{ref:"form",attrs:{model:e.form,rules:e.formRules,"label-width":"160px"}},[r("el-form-item",{attrs:{label:"新闻标题：",prop:"title"}},[r("el-input",{model:{value:e.form.title,callback:function(t){e.$set(e.form,"title",t)},expression:"form.title"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"新闻类型：",prop:"articles_type_id"}},[r("el-cascader",{attrs:{options:e.typeList,props:{multiple:!0,expandTrigger:"hover"},clearable:""},model:{value:e.form.articles_type_id,callback:function(t){e.$set(e.form,"articles_type_id",t)},expression:"form.articles_type_id"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"略缩图显示模式"}},[r("el-radio-group",{model:{value:e.form.picture_type,callback:function(t){e.$set(e.form,"picture_type",t)},expression:"form['picture_type']"}},[r("el-radio",{attrs:{label:1}},[e._v("大图")]),e._v(" "),r("el-radio",{attrs:{label:3}},[e._v("三视图")]),e._v(" "),r("el-radio",{attrs:{label:2}},[e._v("小图")])],1)],1),e._v(" "),r("el-form-item",{attrs:{label:"略缩图："}},[r("div",{staticClass:"remark"},[e._v("*注:为保证显示效果,请上传750px*400px的图片")]),e._v(" "),r("ws-upload-multiple-img",{attrs:{path:e.form.thumb_url,limit:3===e.form.picture_type?3:1},model:{value:e.form.thumb,callback:function(t){e.$set(e.form,"thumb",t)},expression:"form['thumb']"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"视频封面："}},[r("div",{staticClass:"remark"},[e._v("*注:为保证显示效果,请上传750px*400px的图片")]),e._v(" "),r("ws-upload-single-img",{attrs:{path:e.form.video_cover?e.ImageUrl+e.form.video_cover:""},model:{value:e.form.video_cover,callback:function(t){e.$set(e.form,"video_cover",t)},expression:"form['video_cover']"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"插入视频"}},[r("el-radio-group",{model:{value:e.form.videoType,callback:function(t){e.$set(e.form,"videoType",t)},expression:"form['videoType']"}},[r("el-radio",{attrs:{label:0}},[e._v("视频链接")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("本地上传")])],1),e._v(" "),1===e.form.videoType&&"CLOUD"===e.ENV?r("el-upload",{attrs:{"http-request":e.upLoadTXVideo,action:"",drag:"",multiple:""}},[r("i",{staticClass:"el-icon-upload"}),e._v(" "),r("div",{staticClass:"el-upload__text"},[e._v("将文件拖到此处，或"),r("em",[e._v("点击上传")])])]):e._e(),e._v(" "),1===e.form.videoType&&"CLOUD"!==e.ENV?r("ws-upload-single-file",{attrs:{path:e.form.file_url,api:e.videoUploadUrl},model:{value:e.form.file_url,callback:function(t){e.$set(e.form,"file_url",t)},expression:"form['file_url']"}}):r("el-input",{attrs:{placeholder:"请把视频地址粘贴到此处"},model:{value:e.form.file_url,callback:function(t){e.$set(e.form,"file_url",t)},expression:"form['file_url']"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"简介："}},[r("el-input",{attrs:{type:"textarea",rows:"3"},model:{value:e.form.description,callback:function(t){e.$set(e.form,"description",t)},expression:"form.description"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"文章详情"}},[r("ws-editor",{model:{value:e.form.content,callback:function(t){e.$set(e.form,"content",t)},expression:"form['content']"}})],1),e._v(" "),r("el-form-item",{attrs:{label:"查看权限",prop:"unit"}},[r("el-select",{attrs:{placeholder:"请选择查看权限"},model:{value:e.form.read_power,callback:function(t){e.$set(e.form,"read_power",t)},expression:"form.read_power"}},[r("el-option",{attrs:{value:0,label:"全部"}}),e._v(" "),r("el-option",{attrs:{value:1,label:"内部"}}),e._v(" "),r("el-option",{attrs:{value:2,label:"外部"}})],1)],1),e._v(" "),r("el-form-item",{attrs:{label:"是否显示"}},[r("el-radio-group",{model:{value:e.form.is_display,callback:function(t){e.$set(e.form,"is_display",t)},expression:"form.is_display"}},[r("el-radio",{attrs:{label:0}},[e._v("显示")]),e._v(" "),r("el-radio",{attrs:{label:1}},[e._v("隐藏")])],1)],1),e._v(" "),r("div",{staticStyle:{"text-align":"center"}},[r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.submitForm("form")}}},[e._v("保存")]),e._v(" "),r("el-button",{attrs:{type:"primary"},on:{click:function(t){e.$router.back(-1)}}},[e._v("取消")])],1)],1)],1),e._v(" "),r("el-dialog",{attrs:{visible:e.videoUploadProgressVisible,"close-on-click-modal":!1,"close-on-press-escape":!1,"show-close":!1,title:"视频上传中...",width:"360px"},on:{"update:visible":function(t){e.videoUploadProgressVisible=t}}},[r("el-progress",{attrs:{"text-inside":!0,"stroke-width":26,percentage:e.videoUploadProgress}})],1)],1)},[],!1,null,"d01b43b6",null);p.options.__file="modify.vue";t.default=p.exports},qONk:function(e,t,r){}}]);