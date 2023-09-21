<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from products');
        DB::delete('delete from categories');
        DB::delete('delete from counters');
    }

    public function testInsert()
    {
        DB::table('categories')->insert([
            "id" => 'GADGET',
            "name" => "Gadget"
        ]);

        DB::table('categories')->insert([
            "id" => 'FOOD',
            "name" => "Food"
        ]);

        $result = DB::select("select count(id) as total from categories");
        self::assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table("categories")->select(["id", "name"])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insetCategories()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->insetCategories();


        $collection = DB::table("categories")->where(function (Builder $builder) {
            $builder->where("id", "SMARTPHONE");
            $builder->orWhere("id", "LAPTOP");
            // SELECT * FROM categories WHERE (id = 'SMARTPHONE' OR id = 'LAPTOP')
        })->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBeetwen()
    {
        $this->insetCategories();

        $collection = DB::table("categories")
            ->whereBetween("created_at", ["2020-09-10 10:10:10", "2020-11-10 10:10:10"])
            ->get();


        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insetCategories();


        $collection = DB::table("categories")->whereIn("id", ["SMARTPHONE", "LAPTOP"])->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insetCategories();

        $collection = DB::table("categories")
            ->whereNull("description")
            ->get();


        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insetCategories();

        $collection = DB::table("categories")
            ->whereDate("created_at", "2020-10-10")
            ->get();


        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insetCategories();

        DB::table("categories")->where("id", "=", "SMARTPHONE")->update([
            "name" => "Handphone"
        ]);

        $collection = DB::table("categories")->where("name", "=", "Handphone")->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpsert()
    {
        DB::table("categories")->updateOrInsert([
            "id" => "VOUCHER"
        ], [
            "name" => "Voucher",
            "description" => "Ticket and Voucher",
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $collection = DB::table("categories")->where("id", "=", "Voucher")->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {

        $this->seed(CounterSeeder::class);

        DB::table("counters")->where("id", "=", "sample")->increment('counter', 1);

        $collection = DB::table("counters")->where("id", "=", "sample")->get();
        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insetCategories();

        DB::table("categories")->where("id", "=", "SMARTPHONE")->delete();


        $collection = DB::table("categories")->where("id", "=", "SMARTPHONE")->get();
        self::assertCount(0, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }


    public function insertProducts()
    {
        $this->insetCategories();

        DB::table('products')->insert([
            "id" => "1",
            "name" => "Iphone 14 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
        DB::table('products')->insert([
            "id" => "2",
            "name" => "Redmi Note 12 Pro",
            "category_id" => "SMARTPHONE",
            "price" => 18000000
        ]);
    }

    public function testJoin()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->join("categories", "products.category_id", '=', 'categories.id')
            ->select("products.id", "products.name", "products.price", "categories.name as category_name")
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertProducts();

        $collection = DB::table("products")->whereNotNull("id")
            ->orderBy("price", "desc")->orderBy("name", "desc")
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insetCategories();

        $collection = DB::table("categories")
            ->skip(0)
            ->take(2)
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 0; $i < 100; $i++) {
            DB::table('categories')->insert([
                "id" => "CATEGORY - $i",
                "name" => "Category - $i",
                "created_at" => "2020-10-10 10:10:10"
            ]);
        }
    }

    public function testChunk()
    {
        $this->insertManyCategories();

        DB::table("categories")->orderBy("id")
            ->chunk(10, function ($categories) {
                self::assertNotNull($categories);
                Log::info("Start Chunk");
                $categories->each(function ($categories) {
                    Log::info(json_encode($categories));
                });
                Log::info("End Chunk");
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id")->lazy(10)->take(3);
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table("categories")->orderBy("id")->cursor();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testAgrerate()
    {
        $this->insertProducts();

        $result = DB::table("products")->count("id");
        self::assertEquals(2, $result);

        $result = DB::table("products")->min("price");
        self::assertEquals(18000000, $result);

        $result = DB::table("products")->max("price");
        self::assertEquals(20000000, $result);

        $result = DB::table("products")->avg("price");
        self::assertEquals(19000000, $result);

        $result = DB::table("products")->sum("price");
        self::assertEquals(38000000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->select(
                DB::raw("count(id) as total_product"),
                DB::raw("min(price) as min_price"),
                DB::raw("max(price) as max_price")
            )->get();

        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(18000000, $collection[0]->min_price);
        self::assertEquals(20000000, $collection[0]->max_price);
    }

    public function insertProductFood()
    {
        DB::table('products')->insert([
            "id" => "3",
            "name" => "Bakso",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
        DB::table('products')->insert([
            "id" => "4",
            "name" => "Mie Ayam",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
    }


    public function testGrouBy()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->orderBy("category_id", "desc")
            ->get();

        self::assertCount(2, $collection);
        self::assertEquals("SMARTPHONE", $collection[0]->category_id);
        self::assertEquals("FOOD", $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_product);
        self::assertEquals(2, $collection[1]->total_product);
    }

    public function testGrouByHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table("products")
            ->select("category_id", DB::raw("count(*) as total_product"))
            ->groupBy("category_id")
            ->having(DB::raw("count(*)"), ">", 2)
            ->orderBy("category_id", "desc")
            ->get();

        self::assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertProducts();

        DB::transaction(function () {
            $collection = DB::table("products")
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            self::assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insetCategories();

        $paginate = DB::table("categories")->paginate(perPage: 2, page: 2);


        self::assertEquals(2, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        self::assertCount(2, $collection);
        foreach ($collection as $key => $value) {
            Log::info(json_encode($value));
        }
    }

    public function testIterateAllPagination()
    {
        $this->insetCategories();

        $page = 1;

        while (true) {
            $paginate = DB::table("categories")->paginate(perPage: 2, page: $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;

                $collection = $paginate->items();
                self::assertCount(2, $collection);
                foreach ($collection as $key => $value) {
                    Log::info(json_encode($value));
                }
            }
        }
    }

    public function testCursorPagination()
    {
        $this->insetCategories();

        $cursor = "id";

        while (true) {
            $paginate = DB::table('categories')
                ->orderBy('id')
                ->cursorPaginate(perPage: 2, cursor: $cursor);

            foreach ($paginate as $key => $value) {
                self::assertNotNull($value);
                Log::info(json_encode($value));
            }
            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}
