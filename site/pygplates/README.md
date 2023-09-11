# pyGPlates Reconstruction

We use pyGPlates to create reconstruction of a specific age (in Ma), and then use PyGMT to plot the image.

## Models and Scripts
Currently, we have three different models: Default, Marcilly, and Scotese. The corresponding Python scripts will be called by `../generateRecon.php` according to user selection on the website. The scripts share constants and functions, which are located in `Constants.py` and `Utils.py`, respectively.  

For more details about the configurations of the geological models, please contact [Wen Du](mailto:wendu_0911@icloud.com), the author of the Python scripts here and our expert of GPlates/GMT tools.

## Input and Output

### Input
Each script takes two arguments, an `age` and an `out_dir`.
* `age` - The age of the reconstruction, in Ma.
* `out_dir` - Output directory of the generated reconstruction image.

### Output
Each script will output a PNG image file to the location of `out_dir` if no errors detected during reconstruction. No command-line output.

## TODO
* Rename output filenames.
* 
