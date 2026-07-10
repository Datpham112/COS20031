/*=========================
    GLOBAL SEARCH
=========================*/

const searchInput = document.getElementById("dashboardSearch");

const searchResults = document.getElementById("searchResults");

const data = [

{
    type:"Vehicle",
    title:"29H-556.12",
    subtitle:"Refrigerated Truck • Hanoi Depot",
    url:"#"
},

{
    type:"Vehicle",
    title:"51C-889.04",
    subtitle:"Heavy Transport Truck • HCMC Depot",
    url:"#"
},

{
    type:"Vehicle",
    title:"30E-104.77",
    subtitle:"Electric Van • Can Tho Depot",
    url:"#"
},

{
    type:"Driver",
    title:"Le Van Hoa",
    subtitle:"Da Nang • Safety Score 42",
    url:"#"
},

{
    type:"Driver",
    title:"Tran Minh",
    subtitle:"Hanoi • Safety Score 68",
    url:"#"
},

{
    type:"Driver",
    title:"Pham Quoc",
    subtitle:"HCMC • Safety Score 73",
    url:"#"
},

{
    type:"Job",
    title:"JOB-2291",
    subtitle:"Hanoi Workshop",
    url:"#"
},

{
    type:"Job",
    title:"JOB-2288",
    subtitle:"HCMC Workshop",
    url:"#"
},

{
    type:"Job",
    title:"JOB-2285",
    subtitle:"Can Tho Workshop",
    url:"#"
}

];

searchInput.addEventListener("keyup",function(){

    const keyword=this.value.trim().toLowerCase();

    searchResults.innerHTML="";

    if(keyword===""){

        searchResults.style.display="none";

        return;

    }

    const result=data.filter(item=>{

        return(

            item.title.toLowerCase().includes(keyword)

            ||

            item.subtitle.toLowerCase().includes(keyword)

            ||

            item.type.toLowerCase().includes(keyword)

        );

    });

    if(result.length===0){

        searchResults.innerHTML=`
        <div class="no-result">
            No result found
        </div>`;

        searchResults.style.display="block";

        return;

    }

    result.forEach(item=>{

        const div=document.createElement("div");

        div.className="search-item";

        div.innerHTML=`

            <div class="search-type">${item.type}</div>

            <div class="search-title">${item.title}</div>

            <div class="search-sub">${item.subtitle}</div>

        `;

        div.onclick=()=>{

            alert(
                item.type+
                "\n"+
                item.title+
                "\n"+
                item.subtitle
            );

        };

        searchResults.appendChild(div);

    });

    searchResults.style.display="block";

});

document.addEventListener("click",(e)=>{

    if(!e.target.closest(".search-wrapper")){

        searchResults.style.display="none";

    }

});