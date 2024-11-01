(()=>{"use strict";const e=window.wp.blocks,t=JSON.parse('{"UU":"vimeify/videos-table"}'),a=window.React,l=window.wp.i18n,i=window.wp.blockEditor,n=window.wp.components,r=window.wp.element,{UU:o}=t;(0,e.registerBlockType)(o,{attributes:{currentValue:{type:"string"},author:{type:"string",default:"-1"},categories:{type:"array",default:[]},posts_per_page:{type:"string",default:"6"},order:{type:"string",default:"DESC"},orderby:{type:"string",default:"date"},show_pagination:{type:"string",default:"yes"}},edit:({attributes:e,setAttributes:t})=>{const o=(0,i.useBlockProps)(),[s,m]=(0,r.useState)(null),[c,v]=(0,r.useState)(null),{show_pagination:y}=e;return null===s&&wp.apiFetch({path:"/wp/v2/users"}).then((e=>(e=>{const t=[{label:(0,l.__)("Any","vimeify"),value:-1}];m(t.concat(e))})(e.map((e=>({label:e.name,value:e.id})))))),null===c&&wp.apiFetch({path:"/wp/v2/vimeify-category"}).then((e=>(e=>{const t=[{label:(0,l.__)("Any","vimeify"),value:-1}];v(t.concat(e))})(e.map((e=>({label:e.name,value:e.id})))))),(0,a.createElement)(a.Fragment,null,(0,a.createElement)("div",{...o},(0,a.createElement)(i.InspectorControls,null,(0,a.createElement)("div",{className:"vimeify-inspector-controls-block"},(0,a.createElement)("fieldset",null,(0,a.createElement)(n.SelectControl,{label:(0,l.__)("Author","vimeify"),value:e.author,options:s,onChange:e=>t({author:e})})),(0,a.createElement)("fieldset",null,(0,a.createElement)(n.SelectControl,{label:(0,l.__)("Categories","vimeify"),value:e.categories,options:c,onChange:e=>t({categories:e}),multiple:!0})),(0,a.createElement)("fieldset",null,(0,a.createElement)(n.SelectControl,{label:(0,l.__)("Order Direction","vimeify"),value:e.order,options:[{label:(0,l.__)("DESC","vimeify"),value:"desc"},{label:(0,l.__)("ASC","vimeify"),value:"asc"}],onChange:e=>t({order:e})})),(0,a.createElement)("fieldset",null,(0,a.createElement)(n.SelectControl,{label:(0,l.__)("Order By","vimeify"),value:e.orderby,options:[{label:(0,l.__)("Title","vimeify"),value:"title"},{label:(0,l.__)("Date","vimeify"),value:"date"}],onChange:e=>t({orderby:e})})),(0,a.createElement)("fieldset",null,(0,a.createElement)(n.TextControl,{label:(0,l.__)("Videos number","vimeify"),value:e.posts_per_page,onChange:e=>t({posts_per_page:e})})),(0,a.createElement)("fieldset",null,(0,a.createElement)(n.ToggleControl,{label:(0,l.__)("Show Pagination","vimeify"),help:"yes"===y?(0,l.__)("Yes","vimeify"):(0,l.__)("No","vimeify"),checked:"yes"===y,onChange:e=>{t({show_pagination:e?"yes":"no"})}})))),(0,a.createElement)("div",{className:"vimeify-block-preview vimeify-table-wrapper table-responsive "},(0,a.createElement)("table",{className:"vimeify-table table",border:"0"},(0,a.createElement)("thead",null,(0,a.createElement)("tr",null,(0,a.createElement)("th",{className:"vimeify-head-title"},"Title"),(0,a.createElement)("th",{className:"vimeify-head-date"},"Date"),(0,a.createElement)("th",{className:"vimeify-head-actions"},"Actions"))),(0,a.createElement)("tbody",null,(0,a.createElement)("tr",null,(0,a.createElement)("td",{className:"vimeify-row-title"},"Exaple vimeo video #1"),(0,a.createElement)("td",{className:"vimeify-row-date"},"January 01, 2023"),(0,a.createElement)("td",{className:"vimeify-row-actions"},(0,a.createElement)("a",{href:"#",target:"_blank",title:"View"},(0,a.createElement)("span",{className:"vimeify-eye"})))),(0,a.createElement)("tr",null,(0,a.createElement)("td",{className:"vimeify-row-title"},"Exaple vimeo video #2"),(0,a.createElement)("td",{className:"vimeify-row-date"},"January 02, 2023"),(0,a.createElement)("td",{className:"vimeify-row-actions"},(0,a.createElement)("a",{href:"#",target:"_blank",title:"View"},(0,a.createElement)("span",{className:"vimeify-eye"})))),(0,a.createElement)("tr",null,(0,a.createElement)("td",{className:"vimeify-row-title"},"Exaple vimeo video #3"),(0,a.createElement)("td",{className:"vimeify-row-date"},"January 03, 2023"),(0,a.createElement)("td",{className:"vimeify-row-actions"},(0,a.createElement)("a",{href:"#",target:"_blank",title:"View"},(0,a.createElement)("span",{className:"vimeify-eye"})))))))))},save:()=>null})})();