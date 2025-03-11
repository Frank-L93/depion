import { usePage } from "@inertiajs/react";

export default function Navbar(){
const props = usePage().props;
return(
<div className="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
    <h5 className="my-0 mr-md-auto font-weight-normal">Intern De Pion</h5>
    <nav className="my-2 my-md-0 mr-md-3">
      <a className="p-2 text-dark" href="/">Home</a>
      <a className="p-2 text-dark" href="/about">Over</a>
    </nav>

     <div className="dropdown show">
     <a className="btn dropdown-toggle p-2 text-dark" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            {props.user}
    </a>
    <div className="dropdown-menu" aria-labelledby="dropdownMenuLink">
    <a className="dropdown-item" href="/settings">Instellingen</a>
    <a className="dropdown-item" href="/logout">

                Log uit
            </a>

    </div>
    </div>
  </div>
);
}
