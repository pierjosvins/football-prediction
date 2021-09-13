from django.shortcuts import render
import datadotworld as dw
import numpy as np
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.model_selection import StratifiedKFold
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestRegressor,AdaBoostRegressor
from sklearn.svm import SVR
from sklearn.neighbors import KNeighborsRegressor
from sklearn.pipeline import make_pipeline
from sklearn.feature_selection import SelectKBest, f_classif
from sklearn.preprocessing import PolynomialFeatures, StandardScaler
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error


np.seterr(divide='ignore', invalid='ignore')

preprocessor = make_pipeline(PolynomialFeatures(2, include_bias=False),SelectKBest(f_classif, k='all'))
RandomForest = make_pipeline(preprocessor, RandomForestRegressor())
AdaBoost = make_pipeline(preprocessor, AdaBoostRegressor())
SVM = make_pipeline(preprocessor, StandardScaler(), SVR())
KNN = make_pipeline(preprocessor, StandardScaler(), KNeighborsRegressor())
LR = make_pipeline(preprocessor, StandardScaler(), LinearRegression())
list_of_models = {'RandomForest': RandomForest,'AdaBoost':AdaBoost,'SVM':SVM,'KNN':KNN, 'LR': LR}


soccer_df = dw.load_dataset('dcereijo/player-scores', auto_update=True)
leagues = soccer_df.dataframes['leagues']
clubs = soccer_df.dataframes['clubs']
leagues_json = {}
clubs_json = {}

for league_id, league_name in zip(leagues["league_id"], leagues["name"]):
    leagues_json[league_id] = league_name

for key in leagues_json:
    clubs_json[key] = []

for key in leagues_json:
    for club_id, pretty_name, league_id in zip(clubs['club_id'], clubs['pretty_name'], clubs['league_id']):
        if league_id == key:
            clubs_json[key].append((club_id, pretty_name))



def lastGames(team_id):

    games_desc = dw.query('dcereijo/player-scores', 
                     "SELECT date, home_club_id, away_club_id, home_club_goals, away_club_goals  FROM games WHERE home_club_id ='{}' OR away_club_id ='{}' ORDER BY date DESC".format(team_id, team_id))
    games_desc = games_desc.dataframe
    
    # V N D
    game_results = [0, 0, 0]
    goal_scored = []
    goal_conceeded = []
    game_res = []

    for game in zip(games_desc['home_club_id'], games_desc['away_club_id'],games_desc['home_club_goals'], games_desc['away_club_goals']):
        
        if game[0] == team_id and game[2] > game[3]:
            game_results[0] += 1
            game_res.append(2)
            goal_scored.append(game[2])
            goal_conceeded.append(game[3])
            
        if game[0] == team_id and game[2] < game[3]:
            game_results[2] += 1
            game_res.append(0)
            goal_scored.append(game[2])
            goal_conceeded.append(game[3])
            
        
        if game[1] == team_id and game[2] > game[3]:
            game_results[2] += 1
            game_res.append(0)
            goal_scored.append(game[3])
            goal_conceeded.append(game[2])
            
        if game[1] == team_id and game[2] < game[3]:
            game_results[0] += 1
            game_res.append(2)
            goal_scored.append(game[3])
            goal_conceeded.append(game[2])
        
        if game[2] == game[3]:
            game_results[1] += 1
            game_res.append(1)
            goal_scored.append(game[2])
            goal_conceeded.append(game[2])
    
    
    results_percent = [0, 0, 0]
    results_percent[0] = round(float(game_results[0]) / np.sum(game_results), 2)
    results_percent[2] = round(float(game_results[2]) / np.sum(game_results), 2)
    results_percent[1] = round(1.0 - np.sum(results_percent), 2)

    home_teams = []
    away_teams = []
    home_goals = []
    away_goals = []
    dates = []

    for game in zip(games_desc['home_club_id'], games_desc['away_club_id'], games_desc['date'], games_desc['home_club_goals'], games_desc['away_club_goals']):
        
        home_name = clubs[clubs['club_id'] == game[0]].iloc[0]["pretty_name"]
        away_name = clubs[clubs['club_id'] == game[1]].iloc[0]["pretty_name"]
        home_teams.append(home_name)
        away_teams.append(away_name)

        home_goals.append(game[3])
        away_goals.append(game[4])
        dates.append(game[2].strftime('%d-%m-%Y'))
        
    return dates, home_teams, away_teams, home_goals, away_goals, results_percent, goal_scored, goal_conceeded, game_res


def lastConfrations(team_id1, team_id2):
    
    games_desc = dw.query('dcereijo/player-scores', 
                     "SELECT date, home_club_id, away_club_id, home_club_goals, away_club_goals  FROM games WHERE (home_club_id ='{team1}' AND away_club_id ='{team2}') OR (home_club_id ='{team2}' AND away_club_id ='{team1}') ORDER BY date DESC".format(team1=team_id1, team2=team_id2))
    games_desc = games_desc.dataframe

    if len(games_desc) == 0:
        return -1
    
    team1_goal_scored = []
    team1_goal_conceeded = []
    team1_game_results = []
    
    for game in zip(games_desc['home_club_id'], games_desc['away_club_id'],games_desc['home_club_goals'], games_desc['away_club_goals']):
        
        if game[0] == team_id1 and game[2] > game[3]:
            team1_game_results.append(2)
            team1_goal_scored.append(game[2])
            team1_goal_conceeded.append(game[3])
            
        if game[0] == team_id1 and game[2] < game[3]:
            team1_game_results.append(0)
            team1_goal_scored.append(game[2])
            team1_goal_conceeded.append(game[3])
        
        if game[1] == team_id1 and game[2] > game[3]:
            team1_game_results.append(0)
            team1_goal_scored.append(game[3])
            team1_goal_conceeded.append(game[2])
            
        if game[1] == team_id1 and game[2] < game[3]:
            team1_game_results.append(2)
            team1_goal_scored.append(game[3])
            team1_goal_conceeded.append(game[2])
        
        if game[2] == game[3]:
            team1_game_results.append(1)
            team1_goal_scored.append(game[2])
            team1_goal_conceeded.append(game[2])
    
    
    team2_game_results = []

    for i in team1_game_results:
        if i == 0:
            team2_game_results.append(2)
        elif i == 2:
            team2_game_results.append(0)
        else:
            team2_game_results.append(1)

    team1_results = dict((x,team1_game_results.count(x)) for x in set(team1_game_results))
    team2_results = dict((x,team2_game_results.count(x)) for x in set(team2_game_results))
    
    team1_goal_scored = team1_goal_scored[::-1]
    team1_goal_conceeded = team1_goal_conceeded[::-1]

    team1_game_results = team1_game_results[::-1]
    team2_game_results = team2_game_results[::-1]
    
    
    home_teams = []
    away_teams = []
    home_goals = []
    away_goals = []
    dates = []

    for game in zip(games_desc['home_club_id'], games_desc['away_club_id'], games_desc['date'], games_desc['home_club_goals'], games_desc['away_club_goals']):
        
        home_name = clubs[clubs['club_id'] == game[0]].iloc[0]["pretty_name"]
        away_name = clubs[clubs['club_id'] == game[1]].iloc[0]["pretty_name"]
        home_teams.append(home_name)
        away_teams.append(away_name)

        home_goals.append(game[3])
        away_goals.append(game[4])
        dates.append(game[2].strftime('%d-%m-%Y'))
    
    
    return dates, home_teams, away_teams, home_goals, away_goals, team1_results, team1_goal_scored, team1_goal_conceeded, team1_game_results, team2_game_results
    


def prediction(data, isConf = False, isResult=False, length = 5, iterations=10):
    X = []
    y = []
    
    if len(data) < 10 and isConf == False:
        length = 2
    if isConf == True and len(data) > 5:
        length = 3
    else:
        length = 1

    for i in range(length, len(data)):
        X.append(data[i-length:i])
        y.append(data[i])
    X = np.asarray(X)
    y = np.asarray(y)
    X_to_predict = np.asarray([data[-length:]])
    
    if X.shape[0] < 6:
        KNN = make_pipeline(preprocessor, StandardScaler(), KNeighborsRegressor(n_neighbors=1))
        list_of_models["KNN"] = KNN
        
        
    results_dict = {}
    for i in range(iterations):

        model_errors = {}

        x_train, x_test, y_train, y_test = train_test_split(X, y, test_size = 0.2, train_size=0.8)
        for name in list_of_models:

            model = list_of_models[name]
            history = model.fit(x_train,y_train)

            y_pred = model.predict(x_test)
            error = mean_squared_error(y_test, y_pred)
            model_errors[name] = error

        best_models = list(dict(sorted(model_errors.items(), key=lambda item: item[1])).keys())

        results_list = []

        for name in best_models:

            model = list_of_models[name]
            history = model.fit(x_train,y_train)

            y_pred = round(model.predict(X_to_predict)[0])

            if y_pred > 3 and isResult == True:
                y_pred = 3
            results_list.append(y_pred)

        prob_results = dict((x,results_list.count(x) /float(len(results_list))) for x in set(results_list))
        prob_results = dict(sorted(prob_results.items(), key=lambda item: item[1], reverse=True))
        
        
        for key in list(prob_results.keys()):
            if key in list(results_dict.keys()):
                results_dict[key] += round(round(prob_results[key], 2)/float(iterations), 2)
            else:
                results_dict[key] = round(round(prob_results[key], 2)/float(iterations), 2)
        
    results = dict(sorted(results_dict.items(), key=lambda item: item[1], reverse=True))

    return results
        


def home(request):
    if request.method == "POST":
        selected_league = request.POST['league']
        selected_home_team = int(request.POST['home'])
        selected_away_team = int(request.POST['away'])
        if selected_home_team == selected_away_team:
            context = {
                'leagues': leagues_json, 
                'clubs': clubs_json[selected_league],
                'selected_league': selected_league,
                'selected_home_team': selected_home_team,
                'selected_away_team': selected_away_team,
            }
            return render(request, 'prediction/home.php', context=context)

        home_team_name = clubs[clubs['club_id']==selected_home_team].iloc[0]['pretty_name']
        away_team_name = clubs[clubs['club_id']==selected_away_team].iloc[0]['pretty_name']
        

        home_team_games = lastGames(selected_home_team)
        away_team_games = lastGames(selected_away_team)

        zip_home_team_games = zip(home_team_games[0], home_team_games[1], home_team_games[2], home_team_games[3], home_team_games[4])
        zip_away_team_games = zip(away_team_games[0], away_team_games[1], away_team_games[2], away_team_games[3], away_team_games[4])

        home_prediction = (prediction(home_team_games[6][::-1]), prediction(home_team_games[7][::-1]), prediction(home_team_games[8][::-1], isResult=True))
        away_prediction = (prediction(away_team_games[6][::-1]), prediction(away_team_games[7][::-1]), prediction(away_team_games[8][::-1], isResult=True))

        # Confrontations
        
        confrontations = lastConfrations(selected_home_team, selected_away_team)
        if confrontations == -1 :
            context = {
                'leagues': leagues_json, 
                'clubs': clubs_json[selected_league],
                'selected_league': selected_league,
                'selected_home_team': selected_home_team,
                'selected_away_team': selected_away_team,
                'home_team_games': home_team_games,
                'away_team_games': away_team_games,
                'zip_home_team_games': zip_home_team_games,
                'zip_away_team_games': zip_away_team_games,
                'home_prediction': home_prediction,
                'away_prediction': away_prediction,
                'home_team_name': home_team_name,
                'away_team_name': away_team_name,
            }
            return render(request, 'prediction/home.php', context=context)
        else:
            
            zip_confrontation_games = zip(confrontations[0], confrontations[1], confrontations[2], confrontations[3], confrontations[4])
            confrontation_results = confrontations[5]

            confrontation_home_prediction = (prediction(confrontations[6], isConf=True), prediction(confrontations[8], isConf=True))
            confrontation_away_prediction = (prediction(confrontations[7], isConf=True), prediction(confrontations[9], isConf=True))
            n_confrontations = len(confrontations[0]) 
            context = {
                'leagues': leagues_json, 
                'clubs': clubs_json[selected_league],
                'selected_league': selected_league,
                'selected_home_team': selected_home_team,
                'selected_away_team': selected_away_team,
                'home_team_games': home_team_games,
                'away_team_games': away_team_games,
                'zip_home_team_games': zip_home_team_games,
                'zip_away_team_games': zip_away_team_games,
                'home_prediction': home_prediction,
                'away_prediction': away_prediction,
                'zip_confrontation_games': zip_confrontation_games,
                'confrontation_results': confrontation_results,
                'confrontation_home_prediction': confrontation_home_prediction,
                'confrontation_away_prediction': confrontation_away_prediction,
                'n_confrontations': n_confrontations,
                'home_team_name': home_team_name,
                'away_team_name': away_team_name,
            }
            return render(request, 'prediction/home.php', context=context)


    league_id = list(leagues_json.keys())[0]
    return render(request, 'prediction/home.php', {'leagues': leagues_json, 'clubs': clubs_json[league_id]})


def load_clubs(request):
    league_id = request.GET.get('league_id')
    clubs = clubs_json[league_id]

    return render(request, 'prediction/clubs_options.php', {'clubs': clubs})



