import React, { Component } from 'react';
import './App.css';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import moment from 'moment';
import 'moment/locale/en-gb';
import {isMobileOnly} from 'react-device-detect';


const apiURL = 'http://localhost/films/api/films';

class App extends Component {
  constructor(props) {
        super(props);
        this.state = {
            searchfilms: [],
            ownFilms: [],
            inputName: '',
            date: moment(),
            loading: true,
            timeout: 0,
            api_key: document.getElementById("apikey") ? document.getElementById("apikey").innerText : 1,
        };
    }
    componentDidMount() {
        this.getFilms();
    }
    getFilms() {
        this.setState({
            loading: true
        });
        let url = apiURL;
        url+='?api_key='+this.state.api_key;
        console.log("url is " + url);
        fetch(url)
		.then((response) => response.json())
		.then((responseData) => {
            console.log(responseData);
            const formattedFilms = responseData.result.map(film => {
                return {
                    id: film.id,
                    date_seen: moment(film.date_seen).format('DD.MM.YYYY'),
                    title: film.title
                }
            })
            this.setState({
				ownFilms: formattedFilms,
                loading: false
			});
		});
    }
    inputChanged = (event) => {
        this.setState({[event.target.name]: event.target.value});
    }
    dateChanged = (event) => {
        this.setState({date: event});
    }
    handleSubmit = (event) => {
        event.preventDefault();
        if (this.state.inputName.length > 0) {
            let url = apiURL;
            url+='?api_key='+this.state.api_key;
            console.log("url is " + url);
            const film = {date_seen: moment(this.state.date).format('YYYY-MM-DD'),
                         title: this.state.inputName};
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
              body: JSON.stringify(film)
           }).then((response) => response.json())
		      .then((responseData) => {
				  this.getFilms();
			});
        }
    }
    handleDelete = (event) => {
          let url = apiURL;
          url+='/' + event.target.name + '?api_key='+this.state.api_key;
          console.log("url is " + url);
          fetch(url, {
			  method: 'DELETE'
		    }).then((response) => response.json())
		      .then((responseData) => {
				  this.getFilms();
			});
    }
    render() {
        const itemRows = this.state.ownFilms.map((film) => 
          <tr key={film.id}>
            <td className="col-2 table-date">{film.date_seen}</td>
            <td className="table-title">{film.title}</td>
            <td className="col-1 table-delete"><button name={film.id} className="btn btn-danger" onClick={this.handleDelete}>Delete</button></td>
          </tr>
         );
        let table;
        if (!this.state.loading && this.state.ownFilms.length > 0) {
            table = (
            <div>
              <table id="film-table" className="table table-striped mt-3">
                <thead className="thead-light"><tr><th>Date</th><th>Title</th><th></th></tr></thead>
                <tbody>
                  {itemRows}
                </tbody>
            </table>
          </div>
            );
        }
      return (
          <div>
            <h1>Filmlist</h1>
            <form className="form-group row" id="add-form" onSubmit={this.handleSubmit}>
                <div id="datepicker">
                  <DatePicker className="form-control" selected={this.state.date} onChange={this.dateChanged} dateFormat="DD.MM.YYYY" locale="en-gb" readOnly={isMobileOnly} />
                </div>
                <div id="input-title">
                    <input id="input-title" className="form-control" type="text" placeholder="Film name" name="inputName" onChange={this.inputChanged} value={this.state.inputName} />
                </div>
                <div id="add-button">
                  <input type="submit" className="btn btn-primary" value="Save" />
                </div>
          </form>
          {table}
          </div>
    );
    }
}

export default App;
