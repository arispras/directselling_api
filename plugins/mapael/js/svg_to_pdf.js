/**
 * @param {SVGElement} svg
 * @param {Function} callback
 * @param {jsPDF} callback.pdf
 * */
function svg_to_pdf(svg, callback) {
  //saveSvgAsPng(document.getElementById("diagram"), "diagram.png");
  svgAsDataUri(svg, {}, function(svg_uri) {
  // svgAsPngUri(svg, {}, function(svg_uri) {
    var image = document.createElement('img');

    image.src = svg_uri;
    image.onload = function() {
      var canvas = document.createElement('canvas');
      var context = canvas.getContext('2d');
      var doc = new jsPDF('landscape', 'mm',[1500,1500]);
      // Document of 297mm wide and 210mm high
      //new jsPDF('l', 'mm', [297, 210]);
      var dataUrl;

      canvas.width = image.width;
      canvas.height = image.height;
      context.drawImage(image, 0, 0, image.width, image.height);
      dataUrl = canvas.toDataURL('image/jpeg');
      doc.addImage(dataUrl, 'JPEG', 0, 0, image.width, image.height);

      callback(doc);
      // callback(svg_uri );
    }
  });
}

/**
 * @param {string} name Name of the file
 * @param {string} dataUriString
*/
function download_pdf(name, dataUriString) {
  var link = document.createElement('a');
  link.addEventListener('click', function(ev) {
    link.href = dataUriString;
    link.download = name;
    document.body.removeChild(link);
  }, false);
  document.body.appendChild(link);
  link.click();
}



function svg_to_pdf2(svg,svg2, callback) {

  svgAsDataUri(svg, {}, function(svg_uri) {
    var image = document.createElement('img');

    image.src = svg_uri;
    image.onload = function() {
      var canvas = document.createElement('canvas');
      var context = canvas.getContext('2d');
      var doc = new jsPDF('landscape', 'mm',[1500,1500]);
   
      var dataUrl;


      //doc.text(0, 0, 'PETA BPN - BJR ');
     
      canvas.width = image.width;
      canvas.height = image.height;
      context.clearRect( 0 , 0 , canvas.width, canvas.height );
      context.fillStyle="#FFFFFF";
      context.fillRect(0 , 0 , canvas.width, canvas.height);
      context.drawImage(image, 0, 0, image.width, image.height);
      dataUrl = canvas.toDataURL('image/jpeg');
      doc.addImage(dataUrl, 'JPEG', 200, 200, image.width, image.height);

          svgAsDataUri(svg2, {}, function(svg_uri2) {
          var image2 = document.createElement('img');
          image2.src = svg_uri2;
          image2.onload = function() {
            var canvas2 = document.createElement('canvas');
            var context2 = canvas2.getContext('2d');
            //var doc = new jsPDF('landscape', 'mm',[1500,1500]);
         
            var dataUrl2;

            canvas2.width = image2.width;
            canvas2.height = image2.height;
            context2.clearRect( 0 , 0 , canvas.width, canvas.height );
            context2.fillStyle="#FFFFFF";
            context2.fillRect(0 , 0 , canvas.width, canvas.height);
            context2.drawImage(image2, 0, 0, image2.width, image2.height);
            dataUrl2 = canvas2.toDataURL('image/jpeg');
           doc.addImage(dataUrl2, 'JPEG', 200,200, image2.width, image2.height);

            callback(doc);
         
          }
        });


      //callback(doc);
   
    }
  });
}
function svg_to_pdf3(svg,svg2,caption, callback) {

  svgAsDataUri(svg, {}, function(svg_uri) {
    var image = document.createElement('img');

    image.src = svg_uri;
    image.onload = function() {
      var canvas = document.createElement('canvas');
      var context = canvas.getContext('2d');
      var doc = new jsPDF('landscape', 'mm',[1500,1500]);
   
      var dataUrl;


      //doc.text(0, 0, 'PETA BPN - BJR ');
      doc.setFontSize(75);
      doc.text(500, 100, caption);
      canvas.width = image.width;
      canvas.height = image.height;
      context.clearRect( 0 , 0 , canvas.width, canvas.height );
      context.fillStyle="#FFFFFF";
      context.fillRect(0 , 0 , canvas.width, canvas.height);
      context.drawImage(image, 0, 0, image.width, image.height);
      dataUrl = canvas.toDataURL('image/jpeg');
      doc.addImage(dataUrl, 'JPEG', 200, 200, image.width, image.height);

          svgAsDataUri(svg2, {}, function(svg_uri2) {
          var image2 = document.createElement('img');
          image2.src = svg_uri2;
          image2.onload = function() {
            var canvas2 = document.createElement('canvas');
            var context2 = canvas2.getContext('2d');
            //var doc = new jsPDF('landscape', 'mm',[1500,1500]);
         
            var dataUrl2;

            canvas2.width = image2.width;
            canvas2.height = image2.height;
            context2.clearRect( 0 , 0 , canvas.width, canvas.height );
            context2.fillStyle="#FFFFFF";
            context2.fillRect(0 , 0 , canvas.width, canvas.height);
            context2.drawImage(image2, 0, 0, image2.width, image2.height);
            dataUrl2 = canvas2.toDataURL('image/jpeg');
           doc.addImage(dataUrl2, 'JPEG', 200,200, image2.width, image2.height);

            callback(doc);
         
          }
        });


      //callback(doc);
   
    }
  });
}

