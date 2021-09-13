<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Football Matches Prediction</title>
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
        box-sizing: border-box;
    }
    body {
        background: #efefef;
    }
    .cursor {
        cursor: pointer;
    }
    .confrontation-scrollbar {
        position: relative;
        max-height: 300px;
        overflow: auto;
    }
    
    .table-wrapper-scroll-y {
        display: block;
    }
</style>
<body>
    <nav class="navbar navbar-dark bg-dark justify-content-center">
        <div class="">
            <a class="navbar-brand text-center" href="#">Football Matches Prediction</a>
        </div>
    </nav>
    
    <div class="row justify-content-md-center text-center">
        {% if home_team_games %}
        <div class="col col-lg-3">
            <div class="card text-white bg-primary mt-3 mb-3">
                <div class="card-header">{{home_team_name}}</div>
            </div>
            <div onclick="showAndHide('homeResultChart')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Diagramme de résultats <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <canvas id="homeResultChart" height="200"></canvas>

            <div onclick="showAndHide('homeGoalsChart')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Évolution des buts <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <canvas id="homeGoalsChart" height="250"></canvas>

            <div onclick="showAndHide('homeGames')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Derniers matchs <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id="homeGames" class="table-wrapper-scroll-y confrontation-scrollbar">
                <table class="table-sm table table-bordered table-striped mt-2 mb-3">
                    {% if home_team_games %}
                    <tbody>
                        {% for date, home_team, away_team, home_goals, away_goals in zip_home_team_games %}
                            <tr>
                                <td colspan="4">{{date}}</td>
                            </tr>
                            <tr>
                                <td>{{home_team}}</td>
                                <td>{{home_goals}}</td>
                                <td>{{away_goals}}</td>
                                <td>{{away_team}}</td>
                            </tr>
                        {% endfor %}
                    </tbody>
                    {% endif %}
                </table>
            </div>

            <div onclick="showAndHide('homeNextGames')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Predictions Prochain Match <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <table id="homeNextGames" class="table table-striped mt-2 mb-3">
                <tbody>
                    <tr>
                        <td>Buts à marquer</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in home_prediction.0.items %}
                            {{key}}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                    <tr>
                        <td>Buts à concéder</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in home_prediction.1.items %}
                            {{key}}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                    <tr>
                        <td>Résultat final</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in home_prediction.2.items %}
                            {%if key == 2%}V{% elif key == 1%}N{% elif key == 0%}D{% else%}A{% endif %}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {% endif %}
        <div class="col-lg-6">
            <div class="card bg-light mb-3 mt-3">
                <div class="card-header">CONFRONTATIONS</div>
            </div>
            <div onclick="showAndHide('choice')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i>  Choix d'équipes  <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id="choice" class="card border-info bg-light mb-3">
                <div class="card-body">
                    <form method="post" id="choiceForm" data-clubs-url="{% url 'load_clubs' %}" novalidate>{% csrf_token %}
                        <div class="form-group mb-3">
                            <label class="mb-2" for="leagues"><b>Choisir une ligue</b></label>
                            <select class="form-control" id="league" name="league">>
                                {% for league_id, league_name in leagues.items %}
                                <option value="{{league_id}}" {% if league_id == selected_league %}selected{% endif%}>{{league_name}}</option>
                                {% endfor %}
                            </select>
                        </div>
                        <div class="row mb-3" id="clubs">
                            <div class="form-group col col-lg-6">
                                <label class="mb-2" for="home"><b>Home Team</b></label>
                                <select class="form-control" id="home" name="home">
                                    {% for club_id, club_name in clubs %}
                                    <option value={{club_id}} {% if club_id == selected_home_team %}selected{% endif%}>{{club_name}}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="form-group col col-lg-6">
                                <label class="mb-2" for="away"><b>Away Team</b></label>
                                <select class="form-control" id="away" name="away">
                                    {% for club_id, club_name in clubs %}
                                    <option value={{club_id}} {% if club_id == selected_away_team %}selected{% endif%}>{{club_name}}</option>
                                    {% endfor %}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary form-control">Simuler</button>
                        </div>
                    </form>
                </div>
            </div>
            
            {% if zip_confrontation_games %}
            <!-- Statitistiques-->
            <div onclick="showAndHide('statistics')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i>  Statitistiques  <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id='statistics' class="card border-info bg-light mb-3 p-2">
                <table class="table table-bordered table-striped mt-2 mb-3">
                    <tbody>
                        <tr>
                            <td colspan="4">Matchs joués</td>
                        </tr>
                        <tr>
                            <td colspan="4"><b>{{n_confrontations}}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4">Victoires</td>
                        </tr>
                        <tr>
                            <td>{{home_team_name}}</td>
                            <td>
                                <b>{%if confrontation_results.2 %}{{confrontation_results.2}}{%else%}0{%endif%}</b>
                            </td>
                            <td>
                                <b>{%if confrontation_results.0 %}{{confrontation_results.0}}{%else%}0{%endif%}</b>
                            </td>
                            <td>{{away_team_name}}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Matchs nuls</td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <b>{%if confrontation_results.1 %}{{confrontation_results.1}}{%else%}0{%endif%}</b>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dernières confrontations -->
            <div onclick="showAndHide('confrontations')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i>  Dernières Confrontations  <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id='confrontations' class="card border-info bg-light mb-3 p-2">
                <div class="table-wrapper-scroll-y confrontation-scrollbar">
                    <table class="table table-bordered table-striped mt-2 mb-3">
                        <tbody>
                            {% for date, home_team, away_team, home_goals, away_goals in zip_confrontation_games %}
                                <tr>
                                    <td>{{date}}</td>
                                    <td>{{home_team}}</td>
                                    <td>{{home_goals}}</td>
                                    <td>{{away_goals}}</td>
                                    <td>{{away_team}}</td>
                                </tr>
                            {% endfor %}
                        </tbody>
                    </table>
                </div>
            </div>
            <div onclick="showAndHide('predictConfrontations')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i>  Prédictions Confrontation  <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id="predictConfrontations" class="card border-info bg-light mb-3 p-2">
                <table class="table table-bordered table-striped mt-2 mb-3">
                    <tbody>
                        <tr>
                            <td colspan="2"><b>Buts à marquer</b></td>
                        </tr>
                        <tr>
                            <td>{{home_team_name}}</td>
                            <td>{{away_team_name}}</td>
                        </tr>
                        <tr>
                            <td>| 
                                {% for key, value in confrontation_home_prediction.0.items %}
                                {{key}}: {{value|floatformat:2}} |
                                {%endfor%}
                            </td>
                            <td>| 
                                {% for key, value in confrontation_away_prediction.0.items %}
                                {{key}}: {{value|floatformat:2}} |
                                {%endfor%}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Résultats du match</b></td>
                        </tr>
                        <tr>
                            <td>{{home_team_name}}</td>
                            <td>{{away_team_name}}</td>
                        </tr>
                        <tr>
                            <td>| 
                                {% for key, value in confrontation_home_prediction.1.items %}
                                    {% if key == 2%}V{% elif key == 1%}N{% elif key == 0%}D{% else%}A{% endif %}: {{ value | floatformat:2}} |
                                {%endfor%}
                            </td>
                            <td>| 
                                {% for key, value in confrontation_away_prediction.1.items %}
                                    {% if key == 2%}V{% elif key == 1%}N{% elif key == 0%}D{% else%}A{% endif %}: {{ value | floatformat:2}} |
                                {%endfor%}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {% endif %}
        </div>
        {% if away_team_games %}
        <div class="col col-lg-3">
            <div class="card text-white bg-secondary mb-3 mt-3">
                <div class="card-header">{{away_team_name}}</div>
            </div>
            <div onclick="showAndHide('awayResultChart')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Diagramme de résultats <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <canvas id="awayResultChart" height="200"></canvas>

            <div onclick="showAndHide('awayGoalsChart')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Évolution des buts <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <canvas id="awayGoalsChart" height="250"></canvas>

            <div onclick="showAndHide('awayGames')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Derniers matchs <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <div id="awayGames" class="table-wrapper-scroll-y confrontation-scrollbar">
                <table class="table-sm table table-bordered table-striped mt-2 mb-3">
                    {% if home_team_games %}
                    <tbody>
                        {% for date, home_team, away_team, home_goals, away_goals in zip_away_team_games %}
                            <tr>
                                <td colspan="4">{{date}}</td>
                            </tr>
                            <tr>
                                <td>{{home_team}}</td>
                                <td>{{home_goals}}</td>
                                <td>{{away_goals}}</td>
                                <td>{{away_team}}</td>
                            </tr>
                        {% endfor %}
                    </tbody>
                    {% endif %}
                </table>
            </div>

            <div onclick="showAndHide('awayNextGames')" class="cursor card text-white bg-info mb-3 mt-3">
                <div class="card-header">
                    <i class="far fa-eye"></i> Predictions Prochain Match <i class="far fa-eye-slash"></i>
                </div>
            </div>
            <table id="awayNextGames" class="table table-striped mt-2 mb-3">
                <tbody>
                    <tr>
                        <td>Buts à marquer</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in away_prediction.0.items %}
                            {{key}}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                    <tr>
                        <td>Buts à concéder</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in away_prediction.1.items %}
                            {{key}}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                    <tr>
                        <td>Résultat final</td>
                    </tr>
                    <tr>
                        <td>| 
                            {% for key, value in away_prediction.2.items %}
                            {%if key == 2%}V{% elif key == 1%}N{% elif key == 0%}D{% else%}A{% endif %}: {{ value | floatformat:2}} | 
                            {% endfor %}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {% endif %}
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-eMNCOe7tC1doHpGoWe/6oMVemdAVTMs2xqW4mwXrXsW0L84Iytr2wi5v2QjrP/xp" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js" integrity="sha384-cn7l7gDp0eyniUwwAZgrzD06kc/tftFf19TOAs2zVinnD/C7E91j9yyk5//jjpt/" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        /* Loading teams by league*/
        $("#league").change(function () {
            var url = $("#choiceForm").attr("data-clubs-url");
            var leagueId = $(this).val(); 

            $.ajax({  
                url: url,
                data: {
                    'league_id': leagueId 
                },
                success: function (data) { 
                    $("#clubs").html(data);
                }
            });
        });

        function goals(data1, data2)
        {
            const n = data1.length

            const data = {
                labels: Array(n).fill(""),
                datasets: [
                    {
                        label: 'Buts marqués',
                        data: data1.reverse(),
                        fill: false,
                        borderColor: 'rgb(0,128,0)',
                        tension: 0.1
                    },{
                        label: 'Buts Encaissés',
                        data: data2.reverse(),
                        fill: false,
                        borderColor: 'rgb(255, 99, 132)',
                        tension: 0.1
                    }
                ]
            };
            return data;
        }

        function pie(data){
            const results_data = {
                labels: ['Victoire','Nul','Défaite'],
                datasets: [{
                    data: data,
                    backgroundColor: [
                    'rgb(0,128,0)',
                    'rgb(255, 205, 86)',
                    'rgb(255, 99, 132)',
                    
                    ],
                    hoverOffset: 4
                }]
            };

            return results_data
        }
        /* Home Team Charts */
        {% if home_team_games %}
            var ctx_goals = document.getElementById('homeGoalsChart').getContext('2d');
            var myChart = new Chart(ctx_goals, 
                {
                    type: 'line', data: goals({{home_team_games.6}}, {{home_team_games.7}}),
                    options: {
                        scaleShowLabels: false,
                        scales:{
                            xAxes: [{ticks: {display: false}}]
                        },
                    }
            });
            var ctx_goals = document.getElementById('homeResultChart').getContext('2d');
            var myChart = new Chart(ctx_goals, {type: 'pie', data: pie({{home_team_games.5}}),});
        {% endif %}
        
        /* Away Team Charts */
        {% if away_team_games %}
            var ctx_goals = document.getElementById('awayGoalsChart').getContext('2d');
            var myChart = new Chart(ctx_goals, 
                {
                    type: 'line', data: goals({{away_team_games.6}}, {{away_team_games.7}}),
                    options: {
                        scaleShowLabels: false,
                        scales:{
                            xAxes: [{ticks: {display: false}}]
                        },
                    }
            });
            var ctx_goals = document.getElementById('awayResultChart').getContext('2d');
            var myChart = new Chart(ctx_goals, {type: 'pie', data: pie({{away_team_games.5}}),});
        {% endif %}

        function showAndHide(id) {
            var x = document.getElementById(id);
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }

    </script>
</body>
</html>