import React, { Component } from 'react';
import './App.css';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import moment from 'moment';
import 'moment/locale/en-gb';
import {isMobileOnly} from 'react-device-detect';
import Spinner from 'react-spinkit';
import Autosuggest from 'react-autosuggest';

//const apiURL = 'http://localhost/films/api/films';
const apiURL = 'https://marcuskivi.com/films/api/films';

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
            api_key: document.getElementById("apikey") ? document.getElementById("apikey").innerHTML : "1",
            suggestions: []
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
            let formattedFilms = [];
            if (responseData.result) {
                formattedFilms = responseData.result.map(film => {
                    return {
                        id: film.id,
                        date_seen: moment(film.date_seen).format('DD.MM.YYYY'),
                        title: film.title
                    }
                })
            }
            this.setState({
                ownFilms: formattedFilms,
                loading: false
            });
        });
    }
    onChange = (event, { newValue }) => {
        this.setState({
            inputName: newValue
        });
    };
    dateChanged = (event) => {
        this.setState({date: event});
    }
    handleSubmit = (event) => {
        event.preventDefault();
        if (this.state.inputName.length > 0) {
            const film = {
                date_seen: moment(this.state.date).format('YYYY-MM-DD'),
                title: this.state.inputName
            };
            this.setState({
                loading: true,
                inputName: ''
            });
            let url = apiURL;
            url+='?api_key='+this.state.api_key;
            console.log("url is " + url);
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
        this.setState({
            loading: true
        });
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
    getSuggestions = value => {
        const inputValue = value.trim().toLowerCase();
        if (inputValue.length === 0) {
            this.setState({
                suggestions: []
            });
        } else {
            let dataurl = 'https://marcuskivi.com/films/api/tmdb?api_key=' + this.state.api_key + '&s=' + inputValue;
            fetch(dataurl)
            .then((response) => response.json())
            .then((responseData) => {
                let sugs = [];
                if (responseData.results && responseData.results.length > 0) {
                    sugs = responseData.results.slice(0,5);
                    sugs = sugs.map(item => {
                        const posterURL = item.poster_path && item.poster_path.length > 0
                        ? 'https://image.tmdb.org/t/p/w92' + item.poster_path
                        : 'https://via.placeholder.com/92x138.jpg';
                        return {
                            id: item.id,
                            title: item.title,
                            poster: posterURL,
                            year: item.release_date.split("-")[0],
                            overview: item.overview,
                        }
                    });
                }
                console.log(sugs);
                this.setState({
                    suggestions: sugs
                });
            }).catch(() => {});
        }
    };
    onSuggestionsFetchRequested = ({ value }) => {
        clearTimeout(this.state.timeout);
        this.setState({
            timeout: setTimeout(() => {
                this.getSuggestions(value)
            }, 1000)
        });
    };
    onSuggestionsClearRequested = () => {
        this.setState({
            suggestions: []
        });
    };
    getSuggestionValue = suggestion => suggestion.title;
    renderSuggestion = suggestion => (
        <div className="suggestion-item">
            {suggestion.poster && <img src={suggestion.poster} alt={suggestion.title} />}
            <p className="suggestion-title">
                {suggestion.title} <span className="suggestion-year">({suggestion.year})</span>
            </p>
            <p className="suggestion-overview">{suggestion.overview}</p>
        </div>
    );
    render() {
        const itemRows = this.state.ownFilms.map((film) => 
            <tr key={film.id}>
                <td className="col-2 table-date">{film.date_seen}</td>
                <td className="table-title">{film.title}</td>
                <td className="col-1 table-delete"><button name={film.id} className="btn btn-danger" onClick={this.handleDelete}>Delete</button></td>
            </tr>
        );
        let loading;
        let table;
        if (this.state.loading) {
            loading = (
                <div className="loading-container">
                    <Spinner className="loading-circle" fadeIn='quarter' name='line-scale' />
                </div>
            );
        }
        if (this.state.ownFilms.length > 0) {
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
        const value = this.state.inputName;
        const suggestions = this.state.suggestions;
        const inputProps = {
            placeholder: 'Film name',
            value,
            onChange: this.onChange,
            name: 'inputName',
            className: 'form-control'
        };
        return (
            <div>
                <h1>Filmlist</h1>
                <form className="form-group row" id="add-form" onSubmit={this.handleSubmit}>
                    <div id="datepicker">
                        <DatePicker className="form-control" selected={this.state.date} onChange={this.dateChanged} dateFormat="DD.MM.YYYY"
                            locale="en-gb" readOnly={isMobileOnly} />
                    </div>
                    <div id="input-title">
                        <Autosuggest
                            suggestions={suggestions}
                            onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
                            onSuggestionsClearRequested={this.onSuggestionsClearRequested}
                            getSuggestionValue={this.getSuggestionValue}
                            renderSuggestion={this.renderSuggestion}
                            inputProps={inputProps}
                            />
                    </div>
                    <div id="add-button">
                        <input type="submit" className="btn btn-primary" value="Save" />
                    </div>
                </form>
                {loading}
                {table}
            </div>
        );
    }
}
export default App;
