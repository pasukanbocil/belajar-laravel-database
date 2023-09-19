<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from categories');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                "GADGET",
                "Gadget",
                "Gadget Category",
                "2010-10-10 10:10:10"
            ]);

            DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                "FOOD",
                "Food",
                "Food Category",
                "2010-10-10 10:10:10"
            ]);
        });

        $result = DB::select('select * from categories');
        self::assertCount(2, $result);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "GADGET",
                    "Gadget",
                    "Gadget Category",
                    "2010-10-10 10:10:10"
                ]);

                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "GADGET",
                    "Food",
                    "Food Category",
                    "2010-10-10 10:10:10"
                ]);
            });
        } catch (QueryException $error) {
            //expected;
        }

        $result = DB::select('select * from categories');
        self::assertCount(0, $result);
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::transaction(function () {
                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "GADGET",
                    "Gadget",
                    "Gadget Category",
                    "2010-10-10 10:10:10"
                ]);

                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "FOOD",
                    "Food",
                    "Food Category",
                    "2010-10-10 10:10:10"
                ]);
            });
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $result = DB::select('select * from categories');
        self::assertCount(2, $result);
    }

    public function testManualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::transaction(function () {
                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "GADGET",
                    "Gadget",
                    "Gadget Category",
                    "2010-10-10 10:10:10"
                ]);

                DB::insert('insert into categories (id,name,description,created_at)values(?,?,?,?)', [
                    "GADGET",
                    "Food",
                    "Food Category",
                    "2010-10-10 10:10:10"
                ]);
            });
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $result = DB::select('select * from categories');
        self::assertCount(0, $result);
    }
}
