<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
class searchController extends Controller
{


        public function alltable(Request $request)
            {
        $search = request('search');

        $table = (new User)->getTable();
        $columns = Schema::getColumnListing($table);
        $users = User::where(function ($query) use ($columns, $search) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$search}%");
            }
        })->get();
        return response()->json($users);
      
    }


        public function excluding_column(Request $request)
        {
        $search = request('search');

        $table = (new User)->getTable();
        $columns = Schema::getColumnListing($table);
                $columns = array_filter($columns, function($column) {
            return !in_array($column, ['password', 'role','remember_token', 'created_at', 'updated_at']);
        });
        $users = User::where(function ($query) use ($columns, $search) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%{$search}%");
            }
        })->get();
        return response()->json($users);
    
    }
}
