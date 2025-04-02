import { Button } from './UI/Button';

export function MetaBar(props: MetaType & { button: LazyButton; }) {
    const { button, from, to, total, per_page, next } = props;
    return (
        <div className='flex items-center gap-16px py-16px justify-self-end justify-between w-full max-w-limit'>
            <div>Zobrazuji {to ?? 0} z {total ?? 0}</div>
            {(!!next && next > 0) &&
                <div>
                    <Button {...button}>Zobrazit dalších {next}</Button>

                </div>
            }
        </div>


    );
}
