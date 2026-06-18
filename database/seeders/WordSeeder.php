<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Word;
use App\Models\Category;

class WordSeeder extends Seeder
{
    public function run(): void
    {

		$animals = Category::where('slug', 'eng_animals')->firstOrFail();
		$food = Category::where('slug', 'eng_food')->firstOrFail();
		$colors = Category::where('slug', 'eng_colors')->firstOrFail();
		$numbers = Category::where('slug', 'eng_numbers')->firstOrFail();
		$family = Category::where('slug', 'eng_family')->firstOrFail();
		$body = Category::where('slug', 'eng_body-parts')->firstOrFail();

        // Animals
        Word::create(['english' => 'dog', 'portuguese' => 'cachorro', 'example' => 'The dog is happy', 'category_id' => $animals->id]);
        Word::create(['english' => 'cat', 'portuguese' => 'gato', 'example' => 'The cat is sleeping', 'category_id' => $animals->id]);
        Word::create(['english' => 'bird', 'portuguese' => 'pássaro', 'example' => 'The bird is flying', 'category_id' => $animals->id]);
        Word::create(['english' => 'fish', 'portuguese' => 'peixe', 'example' => 'The fish swims', 'category_id' => $animals->id]);
        Word::create(['english' => 'horse', 'portuguese' => 'cavalo', 'example' => 'The horse runs fast', 'category_id' => $animals->id]);
	Word::create(['english' => 'cow', 'portuguese' => 'vaca', 'example' => 'The cow gives us milk.', 'category_id' => $animals->id]);
	Word::create(['english' => 'pig', 'portuguese' => 'porco', 'example' => 'The pig likes to eat corn.', 'category_id' => $animals->id]);
	Word::create(['english' => 'chicken', 'portuguese' => 'galinha', 'example' => 'The chicken lays eggs every day.', 'category_id' => $animals->id]);
	Word::create(['english' => 'sheep', 'portuguese' => 'ovelha', 'example' => 'The sheep has white wool.', 'category_id' => $animals->id]);
	Word::create(['english' => 'duck', 'portuguese' => 'pato', 'example' => 'The duck swims in the lake.', 'category_id' => $animals->id]);
	Word::create(['english' => 'rabbit', 'portuguese' => 'coelho', 'example' => 'The rabbit jumps very fast.', 'category_id' => $animals->id]);
	Word::create(['english' => 'lion', 'portuguese' => 'leão', 'example' => 'The lion is the king of the jungle.', 'category_id' => $animals->id]);
	Word::create(['english' => 'elephant', 'portuguese' => 'elefante', 'example' => 'The elephant has a long trunk.', 'category_id' => $animals->id]);
	Word::create(['english' => 'monkey', 'portuguese' => 'macaco', 'example' => 'The monkey climbs the trees.', 'category_id' => $animals->id]);
	Word::create(['english' => 'tiger', 'portuguese' => 'tigre', 'example' => 'The tiger has orange stripes.', 'category_id' => $animals->id]);
	Word::create(['english' => 'bear', 'portuguese' => 'urso', 'example' => 'The bear eats honey and fish.', 'category_id' => $animals->id]);

        // Food
        Word::create(['english' => 'apple', 'portuguese' => 'maçã', 'example' => 'I eat an apple', 'category_id' => $food->id]);
        Word::create(['english' => 'bread', 'portuguese' => 'pão', 'example' => 'I eat bread', 'category_id' => $food->id]);
        Word::create(['english' => 'milk', 'portuguese' => 'leite', 'example' => 'Drink your milk', 'category_id' => $food->id]);
        Word::create(['english' => 'banana', 'portuguese' => 'banana', 'example' => 'A banana is yellow', 'category_id' => $food->id]);
        Word::create(['english' => 'cheese', 'portuguese' => 'queijo', 'example' => 'I like cheese', 'category_id' => $food->id]);
	Word::create(['english' => 'orange', 'portuguese' => 'laranja', 'example' => 'Oranges are orange and juicy.', 'category_id' => $food->id]);
	Word::create(['english' => 'rice', 'portuguese' => 'arroz', 'example' => 'I eat rice with beans.', 'category_id' => $food->id]);
	Word::create(['english' => 'beans', 'portuguese' => 'feijão', 'example' => 'Beans are black or brown.', 'category_id' => $food->id]);
	Word::create(['english' => 'egg', 'portuguese' => 'ovo', 'example' => 'I like fried eggs.', 'category_id' => $food->id]);
	Word::create(['english' => 'meat', 'portuguese' => 'carne', 'example' => 'The meat is on the grill.', 'category_id' => $food->id]);
	Word::create(['english' => 'chicken', 'portuguese' => 'frango', 'example' => 'Chicken is good with rice.', 'category_id' => $food->id]);
	Word::create(['english' => 'potato', 'portuguese' => 'batata', 'example' => 'French fries are made from potatoes.', 'category_id' => $food->id]);
	Word::create(['english' => 'tomato', 'portuguese' => 'tomate', 'example' => 'Tomatoes are red.', 'category_id' => $food->id]);
	Word::create(['english' => 'carrot', 'portuguese' => 'cenoura', 'example' => 'Carrots are good for your eyes.', 'category_id' => $food->id]);
	Word::create(['english' => 'water', 'portuguese' => 'água', 'example' => 'Drink water every day.', 'category_id' => $food->id]);
	Word::create(['english' => 'juice', 'portuguese' => 'suco', 'example' => 'I drink orange juice.', 'category_id' => $food->id]);
	Word::create(['english' => 'cake', 'portuguese' => 'bolo', 'example' => 'The cake is very sweet.', 'category_id' => $food->id]);
	Word::create(['english' => 'ice cream', 'portuguese' => 'sorvete', 'example' => 'Ice cream is cold and yummy.', 'category_id' => $food->id]);
	Word::create(['english' => 'chocolate', 'portuguese' => 'chocolate', 'example' => 'Children love chocolate.', 'category_id' => $food->id]);
	Word::create(['english' => 'pizza', 'portuguese' => 'pizza', 'example' => 'Pizza has cheese and tomato.', 'category_id' => $food->id]);

        // Colors
        Word::create(['english' => 'red', 'portuguese' => 'vermelho', 'example' => 'The apple is red', 'category_id' => $colors->id]);
        Word::create(['english' => 'blue', 'portuguese' => 'azul', 'example' => 'The sky is blue', 'category_id' => $colors->id]);
        Word::create(['english' => 'green', 'portuguese' => 'verde', 'example' => 'The grass is green', 'category_id' => $colors->id]);
        Word::create(['english' => 'yellow', 'portuguese' => 'amarelo', 'example' => 'The sun is yellow', 'category_id' => $colors->id]);
        Word::create(['english' => 'black', 'portuguese' => 'preto', 'example' => 'The cat is black', 'category_id' => $colors->id]);
	Word::create(['english' => 'orange', 'portuguese' => 'laranja', 'example' => 'The orange is orange.', 'category_id' => $colors->id]);
	Word::create(['english' => 'purple', 'portuguese' => 'roxo', 'example' => 'The flower is purple.', 'category_id' => $colors->id]);
	Word::create(['english' => 'pink', 'portuguese' => 'rosa', 'example' => 'The dress is pink.', 'category_id' => $colors->id]);
	Word::create(['english' => 'brown', 'portuguese' => 'marrom', 'example' => 'The chocolate is brown.', 'category_id' => $colors->id]);
	Word::create(['english' => 'white', 'portuguese' => 'branco', 'example' => 'The snow is white.', 'category_id' => $colors->id]);
	Word::create(['english' => 'gray', 'portuguese' => 'cinza', 'example' => 'The elephant is gray.', 'category_id' => $colors->id]);
	Word::create(['english' => 'violet', 'portuguese' => 'violeta', 'example' => 'The violet is a pretty color.', 'category_id' => $colors->id]);

        // Numbers
        Word::create(['english' => 'one', 'portuguese' => 'um', 'example' => 'I have one apple', 'category_id' => $numbers->id]);
        Word::create(['english' => 'two', 'portuguese' => 'dois', 'example' => 'I have two dogs', 'category_id' => $numbers->id]);
        Word::create(['english' => 'three', 'portuguese' => 'três', 'example' => 'Count to three', 'category_id' => $numbers->id]);
        Word::create(['english' => 'four', 'portuguese' => 'quatro', 'example' => 'Four seasons', 'category_id' => $numbers->id]);
        Word::create(['english' => 'five', 'portuguese' => 'cinco', 'example' => 'High five!', 'category_id' => $numbers->id]);
	Word::create(['english' => 'six', 'portuguese' => 'seis', 'example' => 'I have six crayons.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'seven', 'portuguese' => 'sete', 'example' => 'Seven days in a week.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'eight', 'portuguese' => 'oito', 'example' => 'The spider has eight legs.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'nine', 'portuguese' => 'nove', 'example' => 'Nine planets in stories.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'ten', 'portuguese' => 'dez', 'example' => 'Ten fingers on my hands.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'eleven', 'portuguese' => 'onze', 'example' => 'Eleven players in a soccer team.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'twelve', 'portuguese' => 'doze', 'example' => 'Twelve months in a year.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'thirteen', 'portuguese' => 'treze', 'example' => 'Thirteen is my lucky number.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'fourteen', 'portuguese' => 'quatorze', 'example' => 'I am fourteen years old.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'fifteen', 'portuguese' => 'quinze', 'example' => 'Fifteen minutes to play.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'sixteen', 'portuguese' => 'dezesseis', 'example' => 'Sixteen candles on the cake.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'seventeen', 'portuguese' => 'dezessete', 'example' => 'Seventeen birds in the tree.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'eighteen', 'portuguese' => 'dezoito', 'example' => 'Eighteen colors in my box.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'nineteen', 'portuguese' => 'dezenove', 'example' => 'Nineteen toys on the floor.', 'category_id' => $numbers->id]);
	Word::create(['english' => 'twenty', 'portuguese' => 'vinte', 'example' => 'Twenty fingers and toes!', 'category_id' => $numbers->id]);

        // Family
        Word::create(['english' => 'mother', 'portuguese' => 'mãe', 'example' => 'My mother is kind', 'category_id' => $family->id]);
        Word::create(['english' => 'father', 'portuguese' => 'pai', 'example' => 'My father works', 'category_id' => $family->id]);
        Word::create(['english' => 'brother', 'portuguese' => 'irmão', 'example' => 'I have a brother', 'category_id' => $family->id]);
        Word::create(['english' => 'sister', 'portuguese' => 'irmã', 'example' => 'My sister is funny', 'category_id' => $family->id]);
        Word::create(['english' => 'grandmother', 'portuguese' => 'avó', 'example' => 'Grandmother tells stories', 'category_id' => $family->id]);
	Word::create(['english' => 'grandfather', 'portuguese' => 'avô', 'example' => 'Grandfather is funny.', 'category_id' => $family->id]);
	Word::create(['english' => 'baby', 'portuguese' => 'bebê', 'example' => 'The baby is cute.', 'category_id' => $family->id]);
	Word::create(['english' => 'uncle', 'portuguese' => 'tio', 'example' => 'My uncle plays soccer.', 'category_id' => $family->id]);
	Word::create(['english' => 'aunt', 'portuguese' => 'tia', 'example' => 'Aunt gives me candy.', 'category_id' => $family->id]);
	Word::create(['english' => 'cousin', 'portuguese' => 'primo', 'example' => 'My cousin is my friend.', 'category_id' => $family->id]);
	Word::create(['english' => 'son', 'portuguese' => 'filho', 'example' => 'The son helps his father.', 'category_id' => $family->id]);
	Word::create(['english' => 'daughter', 'portuguese' => 'filha', 'example' => 'My daughter likes to draw.', 'category_id' => $family->id]);
	Word::create(['english' => 'parents', 'portuguese' => 'pais', 'example' => 'Parents love their children.', 'category_id' => $family->id]);
	Word::create(['english' => 'children', 'portuguese' => 'crianças', 'example' => 'The children play together.', 'category_id' => $family->id]);
	Word::create(['english' => 'family', 'portuguese' => 'família', 'example' => 'I love my family.', 'category_id' => $family->id]);

        // Body Parts
        Word::create(['english' => 'head', 'portuguese' => 'cabeça', 'example' => 'My head hurts', 'category_id' => $body->id]);
        Word::create(['english' => 'hand', 'portuguese' => 'mão', 'example' => 'Give me your hand', 'category_id' => $body->id]);
        Word::create(['english' => 'eye', 'portuguese' => 'olho', 'example' => 'I see with my eyes', 'category_id' => $body->id]);
        Word::create(['english' => 'nose', 'portuguese' => 'nariz', 'example' => 'Blow your nose', 'category_id' => $body->id]);
        Word::create(['english' => 'mouth', 'portuguese' => 'boca', 'example' => 'Open your mouth', 'category_id' => $body->id]);
	Word::create(['english' => 'ear', 'portuguese' => 'orelha', 'example' => 'I hear with my ears.', 'category_id' => $body->id]);
	Word::create(['english' => 'hair', 'portuguese' => 'cabelo', 'example' => 'My hair is long.', 'category_id' => $body->id]);
	Word::create(['english' => 'face', 'portuguese' => 'rosto', 'example' => 'Wash your face.', 'category_id' => $body->id]);
	Word::create(['english' => 'neck', 'portuguese' => 'pescoço', 'example' => 'The necklace is on my neck.', 'category_id' => $body->id]);
	Word::create(['english' => 'shoulder', 'portuguese' => 'ombro', 'example' => 'My bag is on my shoulder.', 'category_id' => $body->id]);
	Word::create(['english' => 'arm', 'portuguese' => 'braço', 'example' => 'Raise your arm!', 'category_id' => $body->id]);
	Word::create(['english' => 'finger', 'portuguese' => 'dedo', 'example' => 'I have ten fingers.', 'category_id' => $body->id]);
	Word::create(['english' => 'leg', 'portuguese' => 'perna', 'example' => 'I run with my legs.', 'category_id' => $body->id]);
	Word::create(['english' => 'knee', 'portuguese' => 'joelho', 'example' => 'Bend your knee.', 'category_id' => $body->id]);
	Word::create(['english' => 'foot', 'portuguese' => 'pé', 'example' => 'My foot is ticklish.', 'category_id' => $body->id]);
	Word::create(['english' => 'toe', 'portuguese' => 'dedo do pé', 'example' => 'I have five toes.', 'category_id' => $body->id]);
	Word::create(['english' => 'back', 'portuguese' => 'costas', 'example' => 'My back is strong.', 'category_id' => $body->id]);
	Word::create(['english' => 'tummy', 'portuguese' => 'barriga', 'example' => 'My tummy is full.', 'category_id' => $body->id]);
	Word::create(['english' => 'teeth', 'portuguese' => 'dentes', 'example' => 'Brush your teeth.', 'category_id' => $body->id]);
    }
}
