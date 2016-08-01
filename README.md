# What Is This?

This is a real, 100%, pure Single Page Application :) There is only one index.php containing all necessary logic.

## Story

My father had some kind of certification on his job. So he asked me is it possible to create some kind of learning application if he prepares learning materials (set of pairs question-answer). My task was to put them together.

## Why it's placed in a single index.php?

I suppose every developer knows that mix everything in one place is a bad idea. Especially if we are talking about long-term project support. But also probably you've heard about a trend of "overengineering" among developers: situation when a simple application contains unnecessary complexity and number of decisions based on false predictions.
 
To "vaccinate" myself I've decided to check if I'm still able to do simple things.

## So? Are you able?

Hope so. But you should take in count that simple things differ from primitive ones. So yes - technically all parts of the system are located inside one file. But logically they are isolated from each other.

And what we have under the hood is:

* PHP5.4+
* jQuery (that was not necessary, to be honest)  
* Knockout JS
* Bootstrap
* Vagrant with Ansible as a provision engine. Was not necessary as well but I had working configuration "on my fingers" so I've decided to use it too.

That was fun :) 

Feel free to use this app: you can just modify questions and appropriate answers and use it on your own.