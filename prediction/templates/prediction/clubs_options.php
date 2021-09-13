<div class="form-group col col-lg-6">
    <label class="mb-2" for="home"><b>Home Team</b></label>
    <select class="form-control" id="home" name="home">>
        {% for club_id, club_name in clubs %}
        <option value="{{club_id}}">{{club_name}}</option>
        {% endfor %}
    </select>
</div>
<div class="form-group col col-lg-6">
    <label class="mb-2" for="away"><b>Away Team</b></label>
    <select class="form-control" id="away" name="away">>
        {% for club_id, club_name in clubs %}
        <option value="{{club_id}}">{{club_name}}</option>
        {% endfor %}
    </select>
</div>