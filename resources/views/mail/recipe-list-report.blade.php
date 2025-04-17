<html>
    <body>
        <h1>Recipe List</h1>
        <ul>
            @foreach($recipes as $recipe)
                <li>{{ $recipe->name }}</li>
                <br/>
            @endforeach
        </ul>
    </body>
</html>