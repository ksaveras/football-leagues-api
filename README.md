# Football League API example

Symfony 4.1 API application example with JWT tokens.

## Setup

Docker container php-fpm runs under defined user same as local user id.
Check and update docker-compose.yml user id section. Use same id as your system user.

Build and start Docker containers
```
docker-compose up
```
Install composer packages
```
docker-compose exec php-fpm composer install
```
Run migrations to create required tables
```
docker-compose exec php-fpm bin/console doctrine:migrations:migrate -n
```
Populate demo data fixtures to database
```
docker-compose exec php-fpm bin/console doctrine:fixtures:load -n
```

## Tests

Run tests
```
docker-compose exec php-fpm bin/phpunit
```

## API documentation

All requests must contain valid content type header: `application/json`

### Authentication

Sample application has 2 demo users (in memory provider)
* username: `user`, password: `pass` 
* username: `admin`, password: `admin` 

Test env has separate user (in memory provider) for functional testing
* username: `demo`, password: `demo-pass` 

Send POST request with authentication data in JSON format to /api/login_check:
```
POST /api/login_check
{
    "username": "user",
    "password":"pass"
}
```

JWT token will be generated and returned.
```
{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsb2NhbGhvc3QiLCJpYXQiOjE1NDA3MjQ0NTQsIm5iZiI6MTU0MDcyNDQ1NCwiZXhwIjoxNTQwNzI4MDU0LCJ1aWQiOiJ1c2VyIn0.ExlRVSXqx6keVyotTYOVayKRbcO-hL8BMc1uMGxF0Oo"}
```

Use JWT token in Authorization header:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsb2NhbGhvc3QiLCJpYXQiOjE1NDA3MjQ0NTQsIm5iZiI6MTU0MDcyNDQ1NCwiZXhwIjoxNTQwNzI4MDU0LCJ1aWQiOiJ1c2VyIn0.ExlRVSXqx6keVyotTYOVayKRbcO-hL8BMc1uMGxF0Oo 
```

By default token is valid for one hour.

### Leagues

#### List
```
GET /api/leagues
```

Response:
```json
{
    "leagues": [
        {
            "id": 31,
            "name": "League 1",
            "teams": [
                {
                    "id": 173
                }
            ]
        }
    ]
}
```

#### Create
```
POST /api/leagues
{
    "name": "Super League"
}
```

On success response:
* Status code: 201
* Location header contains link to resource (example: http://localhost/api/leagues/41)

#### Show
```
GET /api/leagues/{id}
```
Response:
```json
{
    "id": 41,
    "name": "League 1",
    "teams": []
}
```

#### Update
```
PUT /api/leagues/{id}
{
    "name": "Demo"
}
```
On success response:
* Status code: 204

#### Delete
```
DELETE /api/leagues/{id}
```
On success response:
* Status code: 204

#### Get all teams in league
```
GET /api/leagues/{id}/teams
```
Response:
```json
{
    "teams": [
        {
            "id": 169,
            "name": "Team 1",
            "strip": "strip 24"
        },
        {
            "id": 170,
            "name": "Team 2",
            "strip": "strip 31"
        }
    ]
}
```

#### Add team to league
```
POST /api/leagues/{id}/teams/{teamId}
```
On success response:
* Status code: 204

#### Remove team from league
```
DELETE /api/leagues/{id}/teams/{teamId}
```
On success response:
* Status code: 204

### Teams

#### List
```
GET /api/teams
```

Response:
```json
{
    "teams": [
        {
            "id": 1,
            "name": "Team 1",
            "strip": "strip-1",
            "leagues": [
                {
                    "id": 1
                },
                {
                    "id": 2
                }
            ]
        }
    ]
}
```

#### Create
```
POST /api/teams
{
    "name": "Super Team",
    "strip": "Random Strip"
}
```

On success response:
* Status code: 201
* Location header contains link to resource (example: http://localhost/api/teams/22)

#### Show
```
GET /api/teams/{id}
```
Response:
```json
{
    "id": 22,
    "name": "Super Team",
    "strip": "Random Strip",
    "leagues": []
}
```

#### Update
```
PUT /api/leagues/{id}
{
    "name": "Demo Team Name"
}
```
On success response:
* Status code: 204

#### Delete
```
DELETE /api/leagues/{id}
```
On success response:
* Status code: 204
