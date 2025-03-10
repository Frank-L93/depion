import { usePage } from "@inertiajs/react";

export default function Navbar(){
const props = usePage().props;
console.log(props);
return(
<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
    <h5 class="my-0 mr-md-auto font-weight-normal">Intern De Pion</h5>
    <nav class="my-2 my-md-0 mr-md-3">
      <a class="p-2 text-dark" href="/">Home</a>
      <a class="p-2 text-dark" href="/about">Over</a>
    </nav>

     <div class="dropdown show">
     <a class="btn dropdown-toggle p-2 text-dark" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            {props.user}
    </a>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
    <a class="dropdown-item" href="/settings">Instellingen</a>
    <a class="dropdown-item" href="/logout">

                Log uit
            </a>

    </div>
    </div>
  </div>
);
}
