<?php

namespace Database\Seeders;

use App\Models\Tag;

/**
 * Nombres de etiquetas para Entre Sabores (≥200). Slugs se derivan con Str::slug.
 */
final class GastronomyTagsCatalog
{
    /**
     * @return array<string, list<string>>
     */
    public static function byType(): array
    {
        return [
            Tag::TYPE_COUNTRY => self::countries(),
            Tag::TYPE_FOOD_TYPE => self::foods(),
            Tag::TYPE_DRINK => self::drinks(),
            Tag::TYPE_EXPERIENCE => self::experiences(),
        ];
    }

    /**
     * @return list<string>
     */
    private static function countries(): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode("\n", <<<'TXT'
Colombia
México
Argentina
Perú
España
Italia
Japón
Francia
Estados Unidos
Brasil
Chile
Ecuador
Venezuela
Uruguay
Paraguay
Bolivia
Costa Rica
Cuba
Panamá
Puerto Rico
República Dominicana
Guatemala
Honduras
El Salvador
Nicaragua
Canadá
Reino Unido
Alemania
Grecia
Portugal
Suiza
Austria
Bélgica
Países Bajos
Irlanda
Noruega
Suecia
Dinamarca
Finlandia
Polonia
Rusia
Ucrania
Tailandia
Vietnam
China
India
Corea del Sur
Singapur
Australia
Nueva Zelanda
Marruecos
Turquía
Israel
Líbano
Sudáfrica
Egipto
Nigeria
Kenia
Islandia
Chipre
Malta
Eslovenia
Eslovaquia
Chequia
Hungría
Rumanía
Bulgaria
Croacia
Serbia
TXT
            ),
        ))));
    }

    /**
     * @return list<string>
     */
    private static function foods(): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode("\n", <<<'TXT'
Dulce
Salado
Arepa
Taco
Tacos al pastor
Empanada
Empanadas argentinas
Sushi
Sashimi
Ramen
Pasta carbonara
Lasagna
Risotto
Pizza napolitana
Pizza al taglio
Hamburguesa gourmet
Hot dog callejero
Burrito
Quesadilla
Enchilada
Tamal
Pozole
Mole
Ceviche
Poke bowl
Pad thai
Curry
Butter chicken
Falafel
Shawarma
Kebab
Paella
Gazpacho
Salmorejo
Tortilla española
Churro
Buñuelo
Buñuelos de bacalao
Buñuelos colombianos
Pan de yuca
Almojábana
Pandebono
Bandeja paisa
Ajiaco
Bandeja paisa light
Lechona
Sancocho
Mondongo
Chunchullo
Chicharrón
Carnitas
Cochinita pibil
Barbacoa
Asado argentino
Churrasco
Feijoada
Moqueca
Acarajé
Vatapá
Feijão tropeiro
Brigadeiro
Quindim
Flan
Tarta de limón
Cheesecake
Brownie
Postre tres leches
Alfajor
Oblea
Arepa de choclo
Arepa boyacense
Arepa santandereana
Patacón
Plátano maduro
Yuca frita
Panzerotti
Calzone
Focaccia
Bruschetta
Caprese
Prosciutto
Jamón ibérico
Salami
Chorizo
Morcilla
Morcilla antioqueña
Butifarra
Longaniza
Trucha
Salmón a la plancha
Pescado frito
Filete de res
Costillas BBQ
Pollo a la brasa
Pollo tikka
Kebab de pollo
Albóndigas
Ñoquis
Ravioles
Tortellini
Spaghetti aglio e olio
Pesto genovés
Ñoquis de papa
Yakisoba
Okonomiyaki
Takoyaki
Gyoza
Dim sum
Bao bun
Spring rolls
Rollitos vietnamitas
Pho
Banh mi
Laksa
Tom yum
Satay
Nasi goreng
Rendang
Goulash
Strudel de manzana
Kaiserschmarrn
Waffles
Pancakes
French toast
Croissant
Pain au chocolat
Beignet
Donut artesanal
Bagel
Sandwich cubano
Club sandwich
TXT
            ),
        ))));
    }

    /**
     * @return list<string>
     */
    private static function drinks(): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode("\n", <<<'TXT'
Café
Espresso
Latte
Cappuccino
Americano
Moka
Cold brew
Affogato
Macchiato
Flat white
Vino tinto
Vino blanco
Vino rosado
Champagne
Prosecco
Cava
Sangría
Clericot
Cerveza artesanal
Cerveza lager
Cerveza IPA
Michelada
Cubra libre
Mojito
Caipirinha
Caipiroska
Piña colada
Margarita
Mezcal
Tequila
Ron añejo
Whisky
Bourbon
Gin tonic
Vermut
Aguardiente
Chicha
Horchata
Atole
Chocolate caliente
Matcha latte
Smoothie
Jugo natural
Limonada
Agua de panela
Té helado
Kombucha
Sidra
Bebidas tradicionales
TXT
            ),
        ))));
    }

    /**
     * @return list<string>
     */
    private static function experiences(): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode("\n", <<<'TXT'
Tradicional
Gourmet
Callejero
Familiar
Romántico
Celebración
Negocios
Brunch
Desayuno de trabajo
Cena de autor
Degustación
Maridaje
Terraza
Vista panorámica
Música en vivo
Pet friendly
Niños bienvenidos
Picnic
Delivery premium
Experiencia chef en casa
Temático vintage
Saludable
Vegano
Vegetariano
Sin gluten
Picante extremo
Comfort food
Late night
After office
Happy hour
TXT
            ),
        ))));
    }
}
