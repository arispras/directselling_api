var nkex=0;
var single = (function() {
    var data = [];

    // initialize
    for(var i = 0; i < nkex; i++) {
        data[i] = [];
    }

    return {
        get: function(i) { // will return an array of items
            return data[i];
        },

        push: function(i, v) { // will add an item
            data[i].push(v);
			nkex++;
        },

        clear: function(i) { // will remove all items
            data[i] = [];
        },

        iterateDefined: function(f,nkex) {
            for(var i = 0; i < nkex; i++) {
                if(data[i].length > 0) {
                    f(data[i], i);
                }
            }
        }
    };
})();

var multi = (function() {
    var data = [];

    // initialize
    for(var i = 0; i < nkex; i++) {
        data[i] = [];
        for(var j = 0; j < nkex; j++) {
            data[i][j] = [];
        }
    }

    return {
        get: function(i, j) { // will return an array of items
            return data[i][j];
        },

        push: function(i, j, v) { // will add an item
            data[i][j].push(v);
			nkex++;
        },

        clear: function(i, j) { // will remove all items
            data[i][j] = [];
        },

        iterateDefined: function(f,nkex) {
            for(var i = 0; i < nkex; i++) {
                for(var j = 0; j < nkex; j++) {
                    if(data[i][j].length > 0) {
                        f(data[i][j], i, j);
                    }
                }
            }
        }
    };
})();

//contoh
/* multi.push(2, 3, { name: "foo", link: "test1" });
multi.push(2, 3, { name: "bar", link: "test2" });

multi.push(1, 4, { name: "haz", link: "test3" });

multi.push(5, 7, { name: "baz", link: "test4" });
multi.clear(5, 7);


console.log(multi.get(2, 3)); // logs an array of 2 items
console.log(multi.get(1, 4)); // logs an array of 1 item
console.log(multi.get(5, 7)); // logs an array of 0 items

console.log(multi.get(2, 3)[0].name); // logs "foo"
console.log(multi.get(2, 3)[1].link); // logs "test2"


multi.iterateDefined(function(items, i, j) {
    console.log(items, i, j); // will log two times
}); */