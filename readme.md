## Installation

### Pré-requis
* PHP >= 7.2
* Composer (https://getcomposer.org/)
* Git (https://git-scm.com/)

### 1. Cloner le projet avec GIT

```bash
$ git clone git@github.com:MehdiMedjdoub/cooking-recipe-api.git
```

Définir les variables d'environnement a partir du fichier .env.dist

### 2. Installer les dépendances
```bash
$ composer install
```

### 3. Création de la base de données et migration

```bash
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```

### 4. Configuration de LexikJWTAuthenticationBundle

```bash
$ mkdir -p config/jwt
$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
Lors de la deuxième commande, vous serez invité à saisir une phrase secrète. Remplissez le fichier .env: 

```
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=YourPassPhrase
###< lexik/jwt-authentication-bundle ###
```

## Usage

### Exemple d'utilisation

#### 1. Pour enregistrer un utilisateur ou se connecter: 

```
[POST] /register 
[POST] /api/login_check
```
Exemple de données à envoyer:
```
{
	"email":"votre email",
	"password":"votre mot de passe"
}
```

Pour la connexion, si tout est ok vous recevez un token en réponse: 
```
{
   "token" : "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJleHAiOjE0MzQ3Mjc1MzYsInVzZXJuYW1lIjoia29ybGVvbiIsImlhdCI6IjE0MzQ2NDExMzYifQ.nh0L_wuJy6ZKIQWh6OrW5hdLkviTs1_bau2GqYdDCB0Yqy_RplkFghsuqMpsFls8zKEErdX5TYCOR7muX0aQvQxGQ4mpBkvMDhJ4-pE4ct2obeMTr_s4X8nC00rBYPofrOONUOR4utbzvbd4d2xT_tj4TdR_0tsr91Y7VskCRFnoXAnNT-qQb7ci7HIBTbutb9zVStOFejrb4aLbr7Fl4byeIEYgp2Gd7gY"
}
```

Passez ensuite le token dans l'en-tête de chaque requête:

```
Authorization: Bearer {token}
```

#### 2. Pour ajouter une nouvelle recette:

```
[POST] /api/recipe
```

Exemple de données à envoyer:
```
{
    "title": "titre de la recette",
    "subtitle": "sous-titre",
    "ingredient": [
        {
            "name": "sucre"
        },
        {
            "name": "beurre"
        },
        {
            "name": "lait"
        }
    ]
}
```

#### 3. Pour récuperer une recette:

```
[GET] /api/recipe/:id
```

Exemple de réponse:
```
{
    "id": 1,
    "title": "titre de la recette",
    "subtitle": "sous-titre",
    "ingredient": [
        {
            "id": 1,
            "name": "sucre"
        },
        {
            "id": 2,
            "name": "beurre"
        },
        {
            "id": 3,
            "name": "lait"
        }
    ]
}
```

#### 4. Pour récuperer la liste de ses recettes

```
[GET] /api/recipes
```

Exemple de réponse:
```
[
    {
        "id": 2,
        "title": "une recette",
        "subtitle": "",
        "ingredient": [
            {
                "id": 4,
                "name": "sucre"
            },
            {
                "id": 5,
                "name": "beurre"
            }
        ]
    },
    {
        "id": 4,
        "title": "une autre recette",
        "subtitle": "recette authentique",
        "ingredient": [
            {
                "id": 8,
                "name": "lait"
            },
            {
                "id": 9,
                "name": "farine"
            },
            {
                "id": 10,
                "name": "oeuf"
            }
        ]
    }
]
```

#### 5. Pour mettre à jour une recette

```
[PUT] /api/recipe/:id
```

Exemple de données à envoyer:
```
{
    "title": "update de la recette",
    "subtitle": "nouveau sous-titre",
    "ingredient": [
        {
            "name": "sucre"
        },
        {
            "name": "beurre"
        },
        {
            "name": "lait"
        }
    ]
}
```

#### 6. Pour supprimer une recette

```
[DELETE] /api/recipe/:id
``
