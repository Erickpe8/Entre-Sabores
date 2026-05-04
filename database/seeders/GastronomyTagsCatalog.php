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
Caribe colombiano
Pacífico colombiano
Región Andina Colombia
Eje cafetero colombiano
Llanos Orientales Colombia
Amazonía colombiana
Centro de México
Sur de México
Península de Yucatán
Bajío mexicano
Norte de México
Nordeste brasileño
Patagonia argentina
NOA gastronómico
Gran Buenos Aires
Costa Caribe suramericana
Mesoamérica culinaria
Cuenca mediterránea europea
Magreb culinario
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
Alta cocina
Cocina casera
Comida callejera
Fast casual
Cocina tradicional
Cocina contemporánea
Cocina de autor
Cocina fusión
Cocina de mercado
Cocina campesina
Cocina costeña
Cocina andina
Cocina amazónica
Cocina mestiza
Tapeo y raciones
Plato fuerte
Entrada fría
Entrada caliente
Acompañamiento
Botana para compartir
Ácido en boca
Amargo equilibrado
Umami marcado
Ahumado profundo
Especiado aromático
Herbáceo fresco
Notas florales
Crujiente
Cremoso
Jugoso
Meloso
Horneado
Frito
Al vapor
Crudo marinado
Guisado lento
A la parrilla
A la brasa
Ahumado en frío
Fermentado en plato
En escabeche
Confitado
Salteado al wok
Rebozado crocante
Glaseado reducido
Curado en sal
Marinado intenso
Picante moderado
Sin picante
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
Bebida artesanal
Bebida fermentada
Bebida espirituosa suave
Coctelería clásica
Coctelería de autor
Mocktail sin alcohol
Mocktail frutal
Destilado artesanal
Licor de hierbas
Amaro digestivo
Digestivo de mesa
Aperitivo servido
Coctel refrescante
Infusión caliente aromática
Infusión fría
Agua fresca tradicional
Refresco de fruta natural
Mate cocido
Tereré frío
Chocolate en bebida
Pulque natural
Tepache casero
Ponche festivo
Colada espesa
Cerveza oscura
Cerveza sin alcohol
Sidra espumante
Vino natural
Vino ancestral
Vino naranja
Jerez y vinos generosos
Vino espumoso
Vino tinto Malbec
Vino tinto Cabernet Sauvignon
Vino tinto Carménère
Vino tinto Syrah
Vino tinto Merlot
Vino tinto Pinot Noir
Vino tinto Bonarda
Vino tinto Tannat
Vino tinto Tempranillo
Vino tinto Garnacha
Vino tinto Petit Verdot
Vino tinto Cabernet Franc
Vino tinto País
Vino blanco Chardonnay
Vino blanco Sauvignon Blanc
Vino blanco Torrontés
Vino blanco Viognier
Vino blanco Riesling
Vino blanco Chenin Blanc
Vino blanco Semillón
Vino blanco Moscatel
Vino blanco Verdejo
Vino blanco Albariño
Rosado de Garnacha
Rosado de Pinot Noir
Clarete
Cava brut
Champagne brut
Espumante brasileño
Espumante argentino brut
Vino joven sin barrica
Vino con crianza en roble
Vino reserva
Vino gran reserva
Vino seco
Vino semi-seco
Vino dulce natural
Perfil afrutado
Perfil herbáceo
Vino natural sin sulfitos
Vino del Valle de Guadalupe
Vino de Parras Coahuila
Vino de Querétaro
Vino de Guanajuato
Vino de Mendoza
Vino de Cafayate Salta
Vino del Valle de Uco
Vino de Maipo
Vino de Colchagua
Vino de Casablanca Chile
Vino del Valle del Itata
Vino de Canelones Uruguay
Vino de Serra Gaúcha
Vino colombiano Valle del Cauca
Vino colombiano Santander
Vino Rioja
Vino Ribera del Duero
Vino Chianti
Vino Barolo
Vino Porto ruby
Jerez fino
Oloroso
Pedro Ximénez
Sangría con vino tinto
Kalimotxo
Tinto de verano
Aguardiente con limón
Aguardiente con soda
Aguardiente sour
Refajo tradicional
Refajo con aguardiente
Canelazo tradicional
Canelazo con puntas
Chicha de maíz
Chicha de arroz
Guarapo con licor
Lulada con aguardiente
Viche
Cuba libre ron colombiano
Mojito ron colombiano
Daiquirí ron colombiano
Piña colada ron colombiano
Limonada de cereza con aguardiente
Tamarindo sour aguardiente
Sangría blanca con aguardiente
Carajillo ron colombiano
Margarita clásica
Margarita frozen
Margarita de mezcal
Margarita de tamarindo
Paloma
Paloma preparada
Paloma picante
Michelada oscura
Michelada cubana
Michelada con clamato
Tequila sunrise
Cantarito jalisciense
Batanga
Charro negro
Vampiro
Mezcal sour
Mezcal paloma
Mezcal negroni
Mezcalita de maracuyá
Mezcalita de mango
Oaxaca old fashioned
Tepache con mezcal
Tamarindo con mezcal
Carajillo
Carajillo Licor 43
Ponche navideño con piquete
Chilate con mezcal
Cuba libre tequila
Clericot mexicano
Sangría mexicana con tequila
Sangrita
Mojito de mezcal
Espresso martini tequila
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
Fiesta patronal
Ritual festivo comunitario
Sobremesa prolongada
Lonche meridiano
Merienda vespertina
Once tradicional
Desayuno relajado
Almuerzo familiar
Cena íntima
Cena social amplia
Consumo en madrugada
Mesa compartida larga
Cotidianidad local
Viaje gastronómico
Descubrimiento de sabores
Nostalgia culinaria
Orgullo regional
Intercambio cultural
Presupuesto accesible
Gama media en mesa
Experiencia alta gama
Celebración íntima
Banquete numeroso
Salida espontánea entre amigos
Terraza al atardecer
Mercado gastronómico
Pop-up efímero
Bar de barrio
Taberna tradicional
Rituales religiosos en mesa
Reunión intergeneracional
Anfitrión en casa
TXT
            ),
        ))));
    }
}
