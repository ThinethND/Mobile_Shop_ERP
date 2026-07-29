import{D as p}from"./Datatable-B6xJgiLF.js";import{d as h,c as f,a as t,b as g,h as _,f as x,P as o,e as v,g as d,m as k}from"./app-DE2QWBiR.js";const w={class:"rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm"},C=h({__name:"BannerList",setup(B){const i=x(()=>o("homebanners.data")),l=v(0),c=[{data:"id",name:"id"},{data:"name",name:"name"},{data:"desktop_image_url",name:"desktop_image_path",orderable:!1,searchable:!1},{data:"mobile_image_url",name:"mobile_image_path",orderable:!1,searchable:!1},{data:"description",name:"description"},{data:"actions",name:"actions",orderable:!1,searchable:!1}],m=[{targets:2,render:e=>e?`
        <img
          src="${e}"
          alt="Desktop Banner"
          class="h-16 w-28 rounded-lg border border-neutral-200 bg-neutral-100 object-cover"
        />
      `:'<span class="text-xs text-neutral-400">No Image</span>'},{targets:3,render:e=>e?`
        <img
          src="${e}"
          alt="Mobile Banner"
          class="h-16 w-28 rounded-lg border border-neutral-200 bg-neutral-100 object-cover"
        />
      `:'<span class="text-xs text-neutral-400">No Image</span>'},{targets:5,render:e=>e}];function u(e){const r=e.target.closest("button[data-action]");if(!r)return;e.preventDefault(),e.stopPropagation();const n=r.dataset.action,s=r.dataset.id,b=r.dataset.name;if(!(!n||!s)){if(n==="edit"){d.visit(o("homebanners.edit",Number(s)));return}if(n==="delete"){if(!confirm(`Delete home banner "${b||""}"? This cannot be undone.`))return;d.delete(o("homebanners.destroy",Number(s)),{preserveScroll:!0,onSuccess:()=>l.value=Date.now()})}}}return(e,a)=>(k(),f("div",w,[a[1]||(a[1]=t("div",{class:"mb-4 font-semibold text-neutral-800"},"All Home Banners",-1)),t("div",{onClick:u},[g(p,{id:"homeBannerTable",url:i.value,columns:c,columnDefs:m,order:[[0,"desc"]],reloadKey:l.value},{header:_(()=>[...a[0]||(a[0]=[t("tr",null,[t("th",{style:{width:"60px"}},"#"),t("th",null,"Banner Name"),t("th",{style:{width:"180px"}},"Desktop Image"),t("th",{style:{width:"180px"}},"Mobile Image"),t("th",null,"Description"),t("th",{style:{width:"220px"}},"Actions")],-1)])]),_:1},8,["url","reloadKey"])])]))}});export{C as _};
