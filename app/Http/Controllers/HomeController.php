<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);

        return response()->json($results);
    }

    public function detectRoboflow(Request $request)
    {
        $image = $request->file('image');
        
        if (!$image) {
            return response()->json(['error' => 'No image uploaded'], 400);
        }
    
        $imagePath = $image->getPathname();
        
        $client = new InferenceHTTPClient(
            api_url: "https://detect.roboflow.com",
            api_key: "Y2RhFee09f8bvvheivuV"
        );
    
        $result = $client->infer($imagePath, model_id: "pbkk-book-search/3");
    
        if (isset($result['predictions'][0])) {
            $predictedClass = $result['predictions'][0]['class'];
            return response()->json(['predicted_class' => $predictedClass]);
        }
    
        return response()->json(['error' => 'No predictions'], 400);
    }
    
}
