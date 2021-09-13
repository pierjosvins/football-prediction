import datadotworld as dw
from django import forms

soccer_df = dw.load_dataset('dcereijo/player-scores')
leagues = soccer_df.dataframes['leagues']
clubs = soccer_df.dataframes['clubs']
leagues_json = {}



def getClubs(a):
    clubs = dw.query('dcereijo/player-scores', 
                     "SELECT club_id, pretty_name FROM clubs WHERE league_id ='{}' ORDER BY pretty_name".format(a[0]))
    clubs = clubs.dataframe
    
    clubs_choices = tuple((club) for club in zip(clubs["club_id"], clubs["pretty_name"]))
    return clubs_choices


class ChoiceForm(forms.Form):

    league_choices = tuple((league_id, leagues_json[league_id]) for league_id in leagues_json)
    
    league = forms.ChoiceField(choices=league_choices)
    club = forms.ChoiceField(choices=getClubs(league))

