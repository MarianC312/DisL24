
const Loading = () => {
    return(
        <div>
            Cargando...
        </div>
    )
}

class UserAlert extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            id: null,
            amount: 12,
            url: "",
            icon: null,
            interval: null
        }
    }

    componentDidMount(){
        let gIcon = this.props.icon;
        this.setState({
            id: this.props.id,
            icon: (typeof gIcon !== 'undefined') ? gIcon : "question-circle-o",
            url: this.props.url
        });
        
    }

    render(){
        const {amount,icon,id} = this.state;
        return(
            <div className="dropdown bg-orange mr-2">
                <button type="button" className="btn user clear" id={"dropdownMenuButtonAlert" + id} data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i className={"fa fa-" + icon}></i>
                    <span className={"badge badge-pill badge-primary " + ((amount == 0) ? "d-none" : "")}>{amount}</span>
                </button>
                <div className="dropdown-menu" aria-labelledby={"dropdownMenuButtonAlert" + id}>
                    {id}
                </div> 
            </div>
        )
    }
}

class User extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            name: "Undefined",
            rol: "undefined",
            company: null,
            companyId: 0,
            id: 0,
            state: "",
            url: {
                data: "./engine/usuario/get-data.php",
                state: "./engine/usuario/get-estado.php"
            },
            interval: null
        }
        this.setData = this.setData.bind(this); 
        this.updateState = this.updateState.bind(this);
    }

    updateState(value){
        this.setState({
            state: (value === "1") ? "activo" : "Inactivo"
        })
    }

    setData(data){
        if(typeof data === "object" && data !== null){
            this.setState({
                name: data.nombre,
                company: data.compañia,
                companyId: data.compañiaId,
                rol: data.rol,
                id: data.id,
                state: (data.estado === "1") ? true : false
            })
        }else{
            console.log(data);
        }
    }

    componentDidMount(){
        this.props.getData(this.state.url.data, this.setData);
        this.props.setInterval(this.updateState, this.props.getData, this.state.url.state, 120);
    }

    render(){
        const {name,company,companyId,rol,id} = this.state;
        return(
            <div className="dropdown bg-orange">
                <button className="btn user clear" type="button" id={"dropdownMenuButton" + id} data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img className="mr-2" src={(companyId > 0) ? "image/compañia/" + companyId + "/logo.png" : "image/logo-standalone.png"} height="35" alt={company} />
                    <div className="d-flex flex-column align-items-baseline mr-2">
                        <span className="user-name">{name}</span>
                        <span className="user-rol">{company + ", " +rol}</span>
                    </div>
                    <i className="fa fa-caret-down"></i>
                </button>
                <div className="dropdown-menu" style={{minWidth: "auto"}} aria-labelledby={"dropdownMenuButton" + id}>
                    <a className="dropdown-item" href="#"><i className="fa fa-cog"></i> Configurar cuenta</a>
                    <a className="dropdown-item" href="#"><i className="fa fa-unlock-alt"></i> Cambiar contraseña</a>
                    <a className="dropdown-item" href="#"><i className="fa fa-envelope"></i> Ver mensajes</a>
                    <div className="dropdown-divider"></div>
                    <a className="dropdown-item" href="#"><i className="fa fa-sign-out"></i> Salir</a>
                </div> 
            </div>
        )
    }
}

class Header extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            code: 2,
            name: null,
            version: "1.0.0", 
            deploy: "",
            interval: null,
            url: "./engine/control/componente-estado.php?id=2",
            error: null
        }
        this.getData = this.getData.bind(this);
        this.setInterval = this.setInterval.bind(this);
        this.setData = this.setData.bind(this);
    }

    setData(data){
        if(typeof data === "object" && data !== null){
            this.setState({
                name: data.nombre,
                version: data.version,
                deploy: (data.estado === "1") ? true : false
            })
        }else{
            console.log(data);
        }
    }

    getData(url, setDataFunction){
        this.serverRequest = 
        axios.request(url)
            .then((result) => {
                    //console.log(result.data);
                    let data = result.data;
                    setDataFunction(result.data);
                    return;
                    this.setState({
                        name: result.data.nombre,
                        version: result.data.version,
                        deploy: (result.data.estado === "1") ? true : false
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

    setInterval(setDataFunction, getDataFunction, url, sec){
        this.setState({
            interval: setInterval(() => {getDataFunction(url, setDataFunction)}, (sec * 1000))
        })
    }

    componentDidMount(){ 
        this.getData(this.state.url, this.setData);
        this.setInterval(this.setData, this.getData, this.state.url, 55);
    } 

    render(){
        const {code,deploy,error} = this.state;
        if(error){
            console.log('Error: ' + error.message);
            return(
                <div>
                    Error al cargar módulo de Usuarios.
                    {console.log("Code: " + code)}
                </div>
            );
        }else{
            if(deploy === true){
                return(
                    <div className="header">
                        <User getData={this.getData} setInterval={this.setInterval} />
                        <div className="d-flex">
                            <UserAlert icon="bell" id={1} getData={this.getData} setInterval={this.setInterval} url="./engine/usuario/get-data-alerta.php" />
                            <UserAlert icon="envelope" id={2} getData={this.getData} setInterval={this.setInterval} url="./engine/usuario/get-data-mensaje.php" />
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
                        <div>
                            Este módulo se encuentra inactivo.
                        </div>
                    )
                }
            }
        }
    }
} 

ReactDOM.render(<Header />, document.getElementById("usuario-header"));