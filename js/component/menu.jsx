const Router = window.ReactRouterDOM.BrowserRouter;
const Route =  window.ReactRouterDOM.Route;
const Link =  window.ReactRouterDOM.Link;
const Prompt =  window.ReactRouterDOM.Prompt;
const Switch = window.ReactRouterDOM.Switch;
const Redirect = window.ReactRouterDOM.Redirect; 
const Copy = () => {
    return(
        <div className="mt-auto text-small text-light font-weight-bold">
            <div className="dropdown-divider"></div>
            2020 <i className="fa fa-copyright"></i> EFECE Soluciones Informáticas
        </div>
    )
}

const Loading = () => {
    return(
        <div>
            Cargando...
        </div>
    )
} 
class Main extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            code: 1,
            name: null,
            version: "1.0.0", 
            deploy: "",
            interval: null,
            collapse: {
                producto: false
            },
            error: null
        };
        this.checkEstado = this.checkEstado.bind(this);
        this.setInterval = this.setInterval.bind(this);
        this.setCollapse = this.setCollapse.bind(this);
        this.logout = this.logout.bind(this);
        this.linkTo = this.linkTo.bind(this);
    }

    linkTo(url, div){
        var me = $(this);
        if (me.data('requestRunning')) {
            return;
        }
        me.data('requestRunning', true);
        this.serverRequest = 
        axios.get(url)
            .then((result) => {
                document.getElementById(div).innerHTML = result.data;
            },
            (error) => {
                console.log(error);
            }
        )
        .catch((error) => {
            console.log(error);
        });
    }

    logout(){
        this.serverRequest = 
        axios.request("./engine/logout.php")
            .then((result) => {
                    console.log(result);
                },
                (error) => {
                    this.setState({
                        isLoaded: true,
                        error
                    });
                }
            )
            .catch(function (error) {
                // handle error
                console.log(error);
            })
        ;
    }

    checkEstado(){
        this.serverRequest = 
        axios.request("./engine/control/componente-estado.php?id=" + this.state.code)
            .then((result) => {
                    this.setState({
                        name: result.data.nombre,
                        version: result.data.version,
                        deploy: (result.data.estado === "1") ? true : false,

                    });
                },
                (error) => {
                    this.setState({
                        deploy: false,
                        error
                    });
                }
            )
            .catch(function (error) {
                // handle error
                console.log(error);
            })
        ;
    }

    setCollapse(e){
        switch(e){
            case "producto":
                this.setState({
                    collapse:{
                        "producto": !this.state.collapse.producto
                    }
                })
            break;
        }
    }

    setInterval(){
        this.setState({
            interval: setInterval(() => {this.checkEstado()}, (60 * 1000))
        })
    }

    componentDidMount() {
        this.checkEstado();
        this.setInterval();
    }

    render(){
        const {deploy,collapse,error} = this.state;
        if(error){
            console.log('Error: ' + error.message);
            return(
                <div className="d-flex flex-column align-items-stretch h-100">
                    <div>
                        Error de módulo.
                    </div>
                    <Copy />
                </div>
            );
        }else{
            if(deploy === true){
                return(
                    <div className="mr-2">
                        <div className="d-flex flex-column align-items-stretch h-100">
                            <nav className="navbar navbar-expand-md navbar-light bg-light flex-column">
                                <a href="#" className="navbar-brand">
                                    <img src="image/logo-standalone.png" height="75" alt="CoolBrand" />
                                </a>
                                <button type="button" className="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                                    <span className="navbar-toggler-icon"></span>
                                </button>
                            
                                <div className="collapse navbar-collapse flex-column w-100" id="navbarCollapse">
                                    <div className="navbar-nav flex-column">
                                        <a href="#/" onClick={(e) => this.linkTo("./members-index.php", "right-content-data")} className="nav-item nav-link active"><i className="fa fa-home"></i> Inicio</a>
                                        <a href="#/" onClick={(e) => this.linkTo("./caja.php", "right-content-data")} className="nav-item nav-link"><i className="fa fa-money"></i> Caja</a>
                                        <a href="#/" onClick={(e) => this.setCollapse("producto"), this.linkTo("./caja.php", "right-content-data") } className="nav-item nav-link" data-toggle="collapse" data-target="#producto-collapse" aria-controls="producto-collapse" aria-haspopup="true" aria-expanded={collapse.producto}><i className="fa fa-product-hunt"></i> Productos</a>
                                        <div className={"collapse w-100" + ((collapse.producto) ? "show" : "")} id="producto-collapse">
                                            <div className="d-flex flex-column ml-2"> 
                                                <a href="./producto.php" className="nav-item nav-link"><i className="fa fa-clipboard"></i> Actividades</a>
                                                <a href="#/" className="nav-item nav-link"><i className="fa fa-caret-right"></i> Registrar</a>
                                                <a href="#/" className="nav-item nav-link"><i className="fa fa-list-ol"></i> Lista</a>
                                                <a href="#/" className="nav-item nav-link"><i className="fa fa-cogs"></i> Administración</a> 
                                            </div>
                                        </div>
                                        <a href="./administracion.php" className="nav-item nav-link" tabIndex="-1"><i className="fa fa-cogs"></i> Administración</a>
                                    </div>
                                    <div className="navbar-nav ml-auto">
                                        <a href="#/" className="nav-item nav-link"><i className="fa fa-sign-out"></i> Salir</a>
                                    </div>
                                </div>
                                <Copy />
                            </nav>
                        </div>
                    </div>
                )
            }else{
                if(deploy === ""){
                    return(
                        <Loading />
                    ) 
                }else{
                    return(
                        <div className="d-flex flex-column align-items-stretch h-100">
                            <div>
                                Este módulo se encuentra inactivo.
                            </div>
                            <Copy />
                        </div>
                    )
                }
            }
        }
    }
} 

ReactDOM.render(<Main />, document.getElementById("left-content"));